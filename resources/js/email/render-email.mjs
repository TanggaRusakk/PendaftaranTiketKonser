import { readFileSync } from 'node:fs';
import { renderToString } from '@vue/server-renderer';
import { createSSRApp, h } from 'vue';
import RegistrationSuccessful from './registration-successful.mjs';

const input = JSON.parse(readFileSync(0, 'utf8'));
const app = createSSRApp({
    render: () => h(RegistrationSuccessful, input),
});

const rendered = await renderToString(app);
const emailHtml = rendered.replace(/^<div>([\s\S]*)<\/div>$/, '$1');

process.stdout.write(`<!doctype html>${emailHtml}`);
