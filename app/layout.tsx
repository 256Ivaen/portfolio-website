import type { Metadata } from 'next'
import { Inter } from 'next/font/google'
import './globals.css'
import Navbar from '../components/Layout/Navbar'
import Footer from '../components/Layout/Footer'

const inter = Inter({ subsets: ['latin'] })

export const metadata: Metadata = {
  title: {
    default: 'Emerge Social Care - Ofsted Registration & Compliance Experts',
    template: '%s | Emerge Social Care'
  },
  description: 'Expert Ofsted registration, compliance support, and advisory services for children homes and supported accommodation providers. Build compliant, quality care services.',
  keywords: 'Ofsted registration, children homes, supported accommodation, Ofsted compliance, social care consultancy, children services, care home registration, Ofsted advisory',
  authors: [{ name: 'Emerge Social Care Advisory & Consulting' }],
  creator: 'Emerge Social Care',
  publisher: 'Emerge Social Care',
  robots: 'index, follow',
  icons: {
    icon: '/favicon.svg',
    shortcut: '/favicon.svg',
    apple: '/favicon.svg',
  },
  openGraph: {
    title: 'Emerge Social Care - Ofsted Registration & Compliance Experts',
    description: 'End-to-end Ofsted registration and compliance support for children homes and supported accommodation providers across the UK.',
    type: 'website',
    locale: 'en_GB',
    siteName: 'Emerge Social Care',
    url: 'https://emergesocialcare.co.uk',
    images: [
      {
        url: '/hero.png',
        width: 1200,
        height: 630,
        alt: 'Emerge Social Care - Building Compliance. Inspiring Quality.',
      },
    ],
  },
  twitter: {
    card: 'summary_large_image',
    title: 'Emerge Social Care - Ofsted Registration & Compliance Experts',
    description: 'Expert Ofsted registration and compliance support for children services',
    creator: '@emergesocialcare',
    images: ['/hero.png'],
  },
  manifest: '/manifest.json',
  metadataBase: new URL('https://emergesocialcare.co.uk'),
  alternates: {
    canonical: '/',
  },
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{
            __html: JSON.stringify({
              "@context": "https://schema.org",
              "@type": "ProfessionalService",
              "name": "Emerge Social Care Advisory & Consulting",
              "description": "Ofsted registration and compliance support for children homes and supported accommodation providers",
              "url": "https://emergesocialcare.co.uk",
              "telephone": "+44-7508-863433",
              "email": "tarisaikadungure@gmail.com",
              "address": {
                "@type": "PostalAddress",
                "addressCountry": "GB"
              },
              "serviceType": [
                "Ofsted Registration",
                "Social Care Compliance", 
                "Children Homes Advisory",
                "Supported Accommodation Support"
              ],
              "areaServed": "United Kingdom"
            })
          }}
        />
      </head>
      <body className={inter.className}>
        <div className="min-h-screen flex flex-col">
          <header className="fixed top-0 left-0 right-0 z-50 bg-white shadow-sm h-16">
            <Navbar />
          </header>
          
          <main className="flex-grow mt-16 min-h-[calc(100vh-64px)]">
            {children}
          </main>
          
          <Footer />
        </div>
      </body>
    </html>
  )
}