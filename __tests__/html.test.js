const fs = require('fs');
const html = fs.readFileSync('telemedicine-html-app.html', 'utf-8');

if (!html.includes('<footer id="footer"') || !html.includes('2025 Telemedicina Platform')) {
  console.error('Footer not found in HTML');
  process.exit(1);
}

console.log('HTML contains expected footer');
