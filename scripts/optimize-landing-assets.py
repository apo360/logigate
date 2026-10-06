"""Create responsive derivatives without changing the supplied originals."""
from pathlib import Path
from PIL import Image
import json

root = Path(__file__).resolve().parents[1]
folder = root / 'public/dist/img/LandingPage'
inventory = []
for file in folder.glob('*.png'):
    with Image.open(file) as image:
        inventory.append({'file': file.name, 'format': image.format, 'width': image.width,
                          'height': image.height, 'bytes': file.stat().st_size, 'mode': image.mode})

for source, prefix, widths in [
    ('Luanda Bay port skyline.png', 'port-desktop', [960, 1600]),
    ('exec-f3ab1d43-e4ec-4cab-a083-999dbf975b96.png', 'port-mobile', [480, 800]),
]:
    with Image.open(folder / source) as image:
        for width in widths:
            resized = image.resize((width, round(image.height * width / image.width)), Image.Resampling.LANCZOS)
            resized.save(folder / f'{prefix}-{width}.webp', 'WEBP', quality=84, method=6)

for source, destination in [
    ('Refined horizontal LogiGate ribbon logo.png', 'logo-horizontal'),
    ('LOGIGATE dark-background logo variant.png', 'logo-light'),
]:
    with Image.open(folder / source) as image:
        # Trim only transparent outer margins. Preserve the emblem and lettering.
        image = image.crop(image.getbbox())
        image.resize((720, round(image.height * 720 / image.width)), Image.Resampling.LANCZOS).save(
            folder / f'{destination}.webp', 'WEBP', lossless=True, method=6)

for file in folder.glob('*.webp'):
    with Image.open(file) as image:
        inventory.append({'file': file.name, 'format': image.format, 'width': image.width,
                          'height': image.height, 'bytes': file.stat().st_size, 'mode': image.mode})
(folder / 'asset-inventory.json').write_text(json.dumps(inventory, indent=2, ensure_ascii=False), encoding='utf-8')
print(json.dumps(inventory, indent=2, ensure_ascii=False))
