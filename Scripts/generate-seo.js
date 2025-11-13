const fs = require('fs');
const path = require('path');

const SITE_URL = 'https://emergesocialcare.co.uk';
const SITE_NAME = 'Emerge Social Care';
const SITE_DESCRIPTION = 'Professional social care services providing compassionate support and care for individuals and families.';

const PAGES = [
  {
    url: '/',
    priority: 1.0,
    changefreq: 'weekly'
  },
  {
    url: '/about',
    priority: 0.8,
    changefreq: 'monthly'
  },
  {
    url: '/services',
    priority: 0.9,
    changefreq: 'monthly'
  },
  {
    url: '/contact',
    priority: 0.7,
    changefreq: 'monthly'
  },
];

function generateSitemap() {
  const sitemap = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${PAGES.map(page => `
  <url>
    <loc>${SITE_URL}${page.url}</loc>
    <lastmod>${new Date().toISOString().split('T')[0]}</lastmod>
    <changefreq>${page.changefreq}</changefreq>
    <priority>${page.priority}</priority>
  </url>
`).join('')}
</urlset>`;

  fs.writeFileSync(path.join('out', 'sitemap.xml'), sitemap);
  console.log('sitemap.xml generated');
}

function generateRobotsTxt() {
  const robots = `User-agent: *
Disallow: /dev/
Disallow: /admin/
Disallow: /test/
Disallow: /staging/

Sitemap: ${SITE_URL}/sitemap.xml`;

  fs.writeFileSync(path.join('out', 'robots.txt'), robots);
  console.log('robots.txt generated');
}

generateSitemap();
generateRobotsTxt();