# Run skill — Billy budget app

Launches the Billy Laravel app and takes screenshots to verify UI changes.

## Start the dev server

```bash
pkill -f "php artisan serve" 2>/dev/null; sleep 1
php artisan serve --port=8765 &> /tmp/billy-serve.log &
for i in {1..20}; do curl -sf http://localhost:8765 > /dev/null && break; sleep 0.5; done
echo "Server up at http://localhost:8765"
```

Logs: `/tmp/billy-serve.log`
Stop: `pkill -f "php artisan serve"`

## Take screenshots (Playwright)

The Playwright script at `/private/tmp/claude-501/-Users-glenallen-Code-Billy-budgeting-app/a91faedf-a51e-4731-bf80-c6d193d01729/scratchpad/screenshot.js` logs in as `glen@example.com` / `password` and screenshots key pages.

To take fresh screenshots of a specific page, write a script to `/tmp/billy-shot.js` and run `node /tmp/billy-shot.js`. Use the pattern:

```js
const { chromium } = require('playwright');
// playwright is installed at:
// /private/tmp/claude-501/-Users-glenallen-Code-Billy-budgeting-app/a91faedf-a51e-4731-bf80-c6d193d01729/scratchpad/node_modules/playwright

(async () => {
  const { chromium } = require('/private/tmp/claude-501/-Users-glenallen-Code-Billy-budgeting-app/a91faedf-a51e-4731-bf80-c6d193d01729/scratchpad/node_modules/playwright');
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  await page.setViewportSize({ width: 1400, height: 900 });

  // Login
  await page.goto('http://localhost:8765/login');
  await page.fill('input[name="email"]', 'glen@example.com');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(3000);

  // Navigate and screenshot
  await page.goto('http://localhost:8765/dashboard');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: '/tmp/billy-dashboard.png' });

  // Mobile view
  await page.setViewportSize({ width: 390, height: 844 });
  await page.screenshot({ path: '/tmp/billy-mobile.png' });

  await browser.close();
})();
```

Screenshots output to `/tmp/billy-*.png` — read them with the Read tool.

## Reset database

```bash
php artisan migrate:fresh --seed
php artisan cache:clear
```

Demo login after seed: `glen@example.com` / `password`

## Hot-reload dev (run in a second terminal)

```bash
npm run dev
```

Vite runs on port 5173 and hot-reloads CSS/JS changes without a page refresh.
