#!/usr/bin/env python3
"""Fail CI if a PHP t('literal') message is absent from gettext catalogs."""
import ast
import pathlib
import re
import sys

root = pathlib.Path(__file__).resolve().parents[1]
sources = [*root.glob('app/**/*.php'), *root.glob('views/**/*.php'), *root.glob('public/**/*.php')]
pattern = re.compile(r"\bt\(\s*'((?:\\'|[^'])*)'\s*\)")
messages = {m.group(1).replace("\\'", "'") for source in sources for m in pattern.finditer(source.read_text())}
catalogs = sorted(root.glob('locales/*/LC_MESSAGES/kodcommerce.po'))
missing = False
for catalog in catalogs:
    text = catalog.read_text()
    entries = set(re.findall(r'^msgid "([^"]+)"$', text, re.MULTILINE))
    absent = sorted(messages - entries)
    if absent:
        print(f'{catalog.relative_to(root)} missing gettext entries: {absent}', file=sys.stderr)
        missing = True
if missing or not catalogs:
    sys.exit(1)
print(f'Checked {len(messages)} literal gettext messages in {len(catalogs)} catalogs.')
