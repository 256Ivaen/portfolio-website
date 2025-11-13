/** @type {import('next-sitemap').IConfig} */
module.exports = {
    siteUrl: 'https://emergesocialcare.co.uk/',
    generateRobotsTxt: true,
    generateIndexSitemap: false,
    outDir: 'out',
    robotsTxtOptions: {
      policies: [
        {
          userAgent: '*',
          allow: '/',
        },
      ],
    },
  }