const fs = require('fs');
const html = fs.readFileSync('telemedicine-html-app.html', 'utf-8');
if (!html.includes('<footer id="footer"') || !html.includes('2025 Telemedicina Platform')) {
  console.error('Footer not found in main HTML');
  process.exit(1);
}

const therapy = fs.readFileSync('patient-therapy.html', 'utf-8');
if (
  !therapy.includes('<h1>Terápiás lap</h1>') ||
  !therapy.includes('id="med-list"') ||
  !therapy.includes('id="dis-list"') ||
  !therapy.includes('id="ther-list"') ||
  !therapy.includes('id="caregiver"') ||
  !therapy.includes('2025 Telemedicina Platform')
) {
  console.error('Therapy page missing expected content');
  process.exit(1);
}

const roleMenuCount = (html.match(/role === 'admin' \|\| role === 'caregiver' \|\| role === 'pharmacist'/g) || []).length;
if (roleMenuCount < 2) {
  console.error('Menu role checks missing for caregiver and pharmacist');
  process.exit(1);
}

console.log('HTML pages contain expected content');
