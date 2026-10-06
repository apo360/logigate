'use strict';
// Functional DOM adapter for database-free tests. It does not replace browser QA.
const vm = require('node:vm');
const assert = require('node:assert/strict');
exports.createDOM = function (html, core) {
    const ids = new Map(), listeners = {}, timers = [], requests = [], storage = new Map();
    let timerId = 0, clock = 0;
    const decode = s => String(s).replace(/&quot;/g, '"').replace(/&#39;/g, "'").replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
    class Element {
        constructor(tag, attrs = {}, parent = null) {
            this.tagName = tag.toUpperCase(); this.attrs = attrs; this.id = attrs.id || ''; this.parent = parent;
            this.dataset = {}; this.listeners = {}; this.children = []; this._html = '';
            this.textContent = ''; this.value = ''; this.open = false; this.isConnected = true;
            this.hidden = Object.hasOwn(attrs, 'hidden'); this.disabled = Object.hasOwn(attrs, 'disabled');
            Object.entries(attrs).forEach(([key, value]) => { if (key.startsWith('data-')) this.dataset[key.slice(5).replace(/-([a-z])/g, (_, c) => c.toUpperCase())] = value; });
            const classes = new Set((attrs.class || '').split(/\s+/));
            this.classList = { add: x => classes.add(x), remove: x => classes.delete(x), contains: x => classes.has(x), toggle: (x, on) => { on ??= !classes.has(x); on ? classes.add(x) : classes.delete(x); return on; } };
            if (this.id) ids.set(this.id, this);
        }
        get innerHTML() { return this._html; }
        set innerHTML(value) { this._html = value; this.children = parse(value, this); }
        insertAdjacentHTML(_position, value) { this.innerHTML += value; }
        setAttribute(key, value) { this.attrs[key] = String(value); }
        getAttribute(key) { return this.attrs[key] ?? null; }
        addEventListener(type, callback) { (this.listeners[type] ??= []).push(callback); }
        fire(type, extra = {}) { const event = { target: this, preventDefault() {}, ...extra }; (this.listeners[type] || []).forEach(fn => fn(event)); }
        closest(selector) {
            let node = this;
            while (node) {
                if ((selector === 'button' || selector === 'button,a') && node.tagName === 'BUTTON') return node;
                if (selector === 'button,a' && node.tagName === 'A') return node;
                if (selector.startsWith('#') && node.id === selector.slice(1)) return node;
                node = node.parent;
            }
            return null;
        }
        querySelectorAll(selector) { return flatten(this.children).filter(el => selector === 'button' && el.tagName === 'BUTTON'); }
        querySelector(selector) { return this.querySelectorAll(selector)[0] || null; }
        focus() { document.activeElement = this; }
        showModal() { this.open = true; }
        close() { this.open = false; this.fire('close'); }
        getBoundingClientRect() { return { left: 0, top: 0, right: 620, bottom: 500 }; }
    }
    function flatten(nodes) { return nodes.flatMap(node => [node, ...flatten(node.children)]); }
    function parse(source, parent) {
        const holder = { children: [] }, stack = [holder], tags = /<(\/?)([a-z][\w-]*)([^>]*?)>/gi;
        const voids = new Set(['META', 'LINK', 'INPUT', 'BR', 'IMG', 'HR']);
        for (const match of source.matchAll(tags)) {
            const [, closing, tag, rest] = match;
            if (closing) { if (stack.length > 1) stack.pop(); continue; }
            const attrs = {};
            for (const attribute of rest.matchAll(/([:\w-]+)(?:\s*=\s*"([^"]*)")?/g)) attrs[attribute[1]] = decode(attribute[2] ?? '');
            const p = stack.at(-1), el = new Element(tag, attrs, p === holder ? parent : p);
            p.children.push(el); if (!voids.has(el.tagName)) stack.push(el);
        }
        return holder.children;
    }
    const tree = parse(html, null);
    const document = {
        body: flatten(tree).find(el => el.tagName === 'BODY'), activeElement: null,
        getElementById(id) { assert(ids.has(id), 'DOM target exists: ' + id); return ids.get(id); },
        addEventListener(type, fn) { (listeners[type] ??= []).push(fn); }
    };
    const location = { origin: 'https://logigate.test', pathname: '/consultar-pauta-aduaneira', search: '', hash: '' };
    const window = { LogiGatePauta: core };
    const sandbox = vm.createContext({
        window, document, location, URL, URLSearchParams, AbortController, console,
        history: { replaceState(_a, _b, url) { const parsed = new URL(url, location.origin); location.pathname = parsed.pathname; location.search = parsed.search; } },
        sessionStorage: { getItem: key => storage.get(key) || null, setItem: (key, value) => storage.set(key, value) },
        setTimeout(fn, ms) { const id = ++timerId; timers.push({ id, at: clock + ms, fn }); return id; },
        clearTimeout(id) { const timer = timers.find(timer => timer.id === id); if (timer) timer.cancelled = true; },
        fetch(url, options = {}) {
            return new Promise((resolve, reject) => requests.push({ url: String(url), options, resolve, reject, settled: false }));
        }
    });
    const flush = async () => { for (let i = 0; i < 12; i++) await Promise.resolve(); };
    const get = id => document.getElementById(id);
    return {
        get, document, location, window, storage, requests, sandbox, flush,
        run(source) { vm.runInContext(source, sandbox); },
        async tick(ms) {
            const end = clock + ms;
            let timer;
            while ((timer = timers.filter(t => !t.cancelled && t.at <= end).sort((a, b) => a.at - b.at)[0])) {
                timer.cancelled = true; clock = timer.at; timer.fn(); await flush();
            }
            clock = end; await flush();
        },
        async respond(match, payload, status = 200) {
            const request = requests.find(r => !r.settled && (typeof match === 'function' ? match(r) : r.url.includes(match)));
            assert(request, 'Pending request: ' + match); request.settled = true;
            request.resolve({ ok: status >= 200 && status < 300, status, json: async () => payload }); await flush();
            return request;
        },
        async click(button) {
            assert(button, 'Rendered button exists'); assert(!button.disabled, 'Button is enabled');
            document.activeElement = button;
            const event = { target: button, preventDefault() {} };
            button.fire('click', event); (listeners.click || []).forEach(fn => fn(event)); await flush();
        },
        button(id, key, value) { return get(id).querySelectorAll('button').find(el => el.dataset[key] === value); }
    };
};
