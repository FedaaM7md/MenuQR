/*
 * MenuQR — real screenshots through Chrome DevTools Protocol.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 *
 * No screenshot library: this uses Node's built-in WebSocket and the locally
 * installed Chrome. Usage: node scripts/screenshots.mjs <baseUrl> <tableToken>
 */

import { spawn } from 'node:child_process';
import { mkdirSync, writeFileSync, rmSync } from 'node:fs';
import { join } from 'node:path';

const base = process.argv[2] ?? 'http://127.0.0.1:8104';
const token = process.argv[3];
const out = join(process.cwd(), 'docs', 'screenshots');
const profile = join(process.cwd(), 'storage', 'app', 'chrome-cdp-profile');
mkdirSync(out, { recursive: true });
rmSync(profile, { recursive: true, force: true });

const chromePath = process.env.CHROME_PATH ?? 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const port = 9333;

const chrome = spawn(chromePath, [
    '--headless=new',
    `--remote-debugging-port=${port}`,
    `--user-data-dir=${profile}`,
    '--no-first-run',
    '--no-default-browser-check',
    '--disable-gpu',
    '--hide-scrollbars',
    'about:blank',
], { stdio: 'ignore' });

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function waitForDebugger() {
    for (let i = 0; i < 60; i++) {
        try {
            const response = await fetch(`http://127.0.0.1:${port}/json/version`);
            if (response.ok) return;
        } catch {
            // Chrome is still starting.
        }
        await sleep(250);
    }
    throw new Error('Chrome debugging endpoint did not start');
}

class Cdp {
    constructor(socket) {
        this.socket = socket;
        this.id = 0;
        this.pending = new Map();
        this.listeners = new Map();
        socket.addEventListener('message', (event) => {
            const message = JSON.parse(event.data);
            if (message.id && this.pending.has(message.id)) {
                const { resolve, reject } = this.pending.get(message.id);
                this.pending.delete(message.id);
                message.error ? reject(new Error(message.error.message)) : resolve(message.result);
            }
            if (message.method && this.listeners.has(message.method)) {
                this.listeners.get(message.method).forEach((callback) => callback(message.params));
            }
        });
    }

    static async connect(url) {
        const socket = new WebSocket(url);
        await new Promise((resolve, reject) => {
            socket.addEventListener('open', resolve, { once: true });
            socket.addEventListener('error', reject, { once: true });
        });
        return new Cdp(socket);
    }

    send(method, params = {}) {
        const id = ++this.id;
        this.socket.send(JSON.stringify({ id, method, params }));
        return new Promise((resolve, reject) => this.pending.set(id, { resolve, reject }));
    }

    once(method) {
        return new Promise((resolve) => {
            const callback = (params) => {
                const list = this.listeners.get(method) ?? [];
                this.listeners.set(method, list.filter((entry) => entry !== callback));
                resolve(params);
            };
            this.listeners.set(method, [...(this.listeners.get(method) ?? []), callback]);
        });
    }

    close() {
        this.socket.close();
    }
}

async function createPage() {
    const response = await fetch(`http://127.0.0.1:${port}/json/new?about:blank`, { method: 'PUT' });
    const target = await response.json();
    const cdp = await Cdp.connect(target.webSocketDebuggerUrl);
    await cdp.send('Page.enable');
    await cdp.send('Runtime.enable');
    await cdp.send('Network.enable');
    return { cdp, target };
}

async function viewport(cdp, { width, height, deviceScaleFactor = 1, mobile = false }) {
    await cdp.send('Emulation.setDeviceMetricsOverride', {
        width, height, deviceScaleFactor, mobile,
    });
    await cdp.send('Emulation.setTouchEmulationEnabled', { enabled: mobile });
}

async function navigate(cdp, url) {
    const loaded = cdp.once('Page.loadEventFired');
    await cdp.send('Page.navigate', { url: base + url });
    await loaded;
    await cdp.send('Runtime.evaluate', { expression: 'document.fonts.ready', awaitPromise: true });
    await sleep(250);
}

async function evaluate(cdp, expression) {
    const result = await cdp.send('Runtime.evaluate', { expression, awaitPromise: true, returnByValue: true });
    return result.result?.value;
}

async function screenshot(cdp, name, options = {}) {
    let clip;
    if (options.fullPage) {
        const metrics = await cdp.send('Page.getLayoutMetrics');
        const size = metrics.cssContentSize ?? metrics.contentSize;
        clip = { x: 0, y: 0, width: Math.ceil(size.width), height: Math.ceil(size.height), scale: 1 };
    }
    const result = await cdp.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: Boolean(options.fullPage),
        ...(clip ? { clip } : {}),
    });
    writeFileSync(join(out, name), Buffer.from(result.data, 'base64'));
    console.log('shot', name);
}

async function overflowCheck(cdp, label) {
    const result = await evaluate(cdp, '({scroll: document.documentElement.scrollWidth, inner: window.innerWidth})');
    if (result.scroll > result.inner + 1) {
        console.log(`OVERFLOW ${label}: ${result.scroll} > ${result.inner}`);
    }
    return result;
}

async function demoLogin(cdp, role) {
    await navigate(cdp, '/login');
    const target = `form[action$="/demo-login"] input[value="${role}"]`;
    await evaluate(cdp, `document.querySelector('${target}').closest('form').submit()`);
    await cdp.once('Page.loadEventFired').catch(() => {});
    await sleep(600);
    await navigate(cdp, '/locale/ar');
}

const desktop = { width: 1440, height: 1000 };
const laptop = { width: 1024, height: 900 };
const tablet = { width: 768, height: 1024 };
const mobile = { width: 375, height: 812, deviceScaleFactor: 2, mobile: true };

try {
    await waitForDebugger();

    const guest = await createPage();
    await viewport(guest.cdp, desktop);
    await navigate(guest.cdp, '/');
    await overflowCheck(guest.cdp, 'home desktop');
    await screenshot(guest.cdp, 'home-ar-desktop.png');
    await viewport(guest.cdp, mobile);
    await navigate(guest.cdp, '/');
    await overflowCheck(guest.cdp, 'home mobile');
    await screenshot(guest.cdp, 'home-ar-mobile.png');

    await navigate(guest.cdp, '/locale/en');
    await viewport(guest.cdp, desktop);
    await navigate(guest.cdp, '/');
    await screenshot(guest.cdp, 'home-en-desktop.png');
    await viewport(guest.cdp, mobile);
    await navigate(guest.cdp, '/');
    await screenshot(guest.cdp, 'home-en-mobile.png');

    await viewport(guest.cdp, desktop);
    await navigate(guest.cdp, '/login');
    await screenshot(guest.cdp, 'login-en-desktop.png');
    await viewport(guest.cdp, mobile);
    await navigate(guest.cdp, '/login');
    await overflowCheck(guest.cdp, 'login mobile');
    await screenshot(guest.cdp, 'login-en-mobile.png');

    await navigate(guest.cdp, '/locale/ar');
    await viewport(guest.cdp, desktop);
    await navigate(guest.cdp, '/about');
    await screenshot(guest.cdp, 'about-ar-desktop.png');
    await viewport(guest.cdp, mobile);
    await navigate(guest.cdp, '/contact');
    await overflowCheck(guest.cdp, 'contact mobile');
    await screenshot(guest.cdp, 'contact-ar-mobile.png');

    await viewport(guest.cdp, desktop);
    await navigate(guest.cdp, '/login');
    await screenshot(guest.cdp, 'login-ar-desktop.png');
    await viewport(guest.cdp, mobile);
    await navigate(guest.cdp, '/login');
    await screenshot(guest.cdp, 'login-ar-mobile.png');

    if (token) {
        await viewport(guest.cdp, mobile);
        await navigate(guest.cdp, `/m/${token}`);
        await overflowCheck(guest.cdp, 'menu mobile');
        await screenshot(guest.cdp, 'menu-ar-mobile.png', { fullPage: true });

        const cartLoaded = guest.cdp.once('Page.loadEventFired');
        await evaluate(guest.cdp, `(() => {
            const form = [...document.querySelectorAll('form')].find((item) => item.action.endsWith('/cart') && item.querySelector('input[name="item_id"]'));
            if (form) {
                form.requestSubmit();
                return true;
            }
            return false;
        })()`);
        await cartLoaded;
        await sleep(700);
        await overflowCheck(guest.cdp, 'menu mobile cart');
        await screenshot(guest.cdp, 'menu-ar-mobile-cart.png');

        await navigate(guest.cdp, '/locale/en');
        await navigate(guest.cdp, `/m/${token}`);
        await screenshot(guest.cdp, 'menu-en-mobile.png', { fullPage: true });
        await viewport(guest.cdp, desktop);
        await navigate(guest.cdp, `/m/${token}`);
        await screenshot(guest.cdp, 'menu-en-desktop.png');
    }

    await guest.cdp.send('Network.clearBrowserCookies');

    await demoLogin(guest.cdp, 'admin');
    await viewport(guest.cdp, desktop);
    await navigate(guest.cdp, '/admin');
    await screenshot(guest.cdp, 'dashboard-ar-desktop.png');
    for (const [name, path] of [
        ['items-ar-desktop.png', '/admin/items'],
        ['tables-ar-desktop.png', '/admin/tables'],
        ['reports-ar-desktop.png', '/admin/reports'],
        ['activity-ar-desktop.png', '/admin/activity'],
        ['settings-ar-desktop.png', '/admin/settings'],
    ]) {
        await navigate(guest.cdp, path);
        await screenshot(guest.cdp, name);
    }
    await viewport(guest.cdp, laptop);
    await navigate(guest.cdp, '/admin');
    await overflowCheck(guest.cdp, 'dashboard 1024');
    await screenshot(guest.cdp, 'dashboard-ar-1024.png');
    await viewport(guest.cdp, { width: 1366, height: 900 });
    await navigate(guest.cdp, '/admin/items');
    await overflowCheck(guest.cdp, 'items 1366');
    await screenshot(guest.cdp, 'items-ar-1366.png');
    await viewport(guest.cdp, tablet);
    await navigate(guest.cdp, '/admin/items');
    await overflowCheck(guest.cdp, 'items 768');
    await screenshot(guest.cdp, 'items-ar-768.png');
    await viewport(guest.cdp, mobile);
    await navigate(guest.cdp, '/admin');
    await overflowCheck(guest.cdp, 'dashboard mobile');
    await screenshot(guest.cdp, 'dashboard-ar-mobile.png');
    await evaluate(guest.cdp, "document.querySelector('header button[aria-label]')?.click()");
    await sleep(450);
    await screenshot(guest.cdp, 'dashboard-ar-mobile-menu.png');
    await evaluate(guest.cdp, "document.querySelector('header button[aria-label]')?.click()");
    await sleep(250);
    await navigate(guest.cdp, '/admin/items');
    await overflowCheck(guest.cdp, 'items mobile');
    await screenshot(guest.cdp, 'items-ar-mobile.png');
    await navigate(guest.cdp, '/staff/orders');
    await overflowCheck(guest.cdp, 'orders mobile');
    await screenshot(guest.cdp, 'orders-ar-mobile.png');

    await navigate(guest.cdp, '/locale/en');
    await viewport(guest.cdp, desktop);
    await navigate(guest.cdp, '/admin');
    await screenshot(guest.cdp, 'dashboard-en-desktop.png');

    await guest.cdp.send('Network.clearBrowserCookies');
    await demoLogin(guest.cdp, 'kitchen');
    await viewport(guest.cdp, desktop);
    await navigate(guest.cdp, '/staff/kitchen');
    await screenshot(guest.cdp, 'kitchen-ar-desktop.png');
    for (const [name, size] of [
        ['kitchen-ar-1024.png', laptop],
        ['kitchen-ar-768.png', tablet],
        ['kitchen-ar-mobile.png', mobile],
    ]) {
        await viewport(guest.cdp, size);
        await navigate(guest.cdp, '/staff/kitchen');
        await overflowCheck(guest.cdp, name);
        await screenshot(guest.cdp, name);
    }

    guest.cdp.close();
    console.log('done');
} finally {
    chrome.kill();
    await sleep(1000);
    try {
        rmSync(profile, { recursive: true, force: true, maxRetries: 3, retryDelay: 200 });
    } catch {
        // Chrome may still hold the profile briefly; it is git-ignored.
    }
}
