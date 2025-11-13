import type { Metadata } from 'next'
import Hero from '../components/Home/Hero'
import FAQWithSpiral from '@/components/ui components/FAQs'
import Stats from '@/components/ui components/Stats'
import BlogsPage from '@/components/Home/Blogs'
import WhyWorkWithUs from '@/components/Home/WhyUs'
import AboutSection from '@/components/Home/About'
import CTASection from '@/components/Home/CTAsection'
import ServicesSection from '@/components/Home/Services'

export const metadata: Metadata = {
  title: 'Emerge Social Care - Ofsted Registration & Compliance Experts',
  description: 'Expert Ofsted registration, compliance support, and advisory services for children homes and supported accommodation providers. Build compliant, quality care services across the UK.',
  keywords: 'Ofsted registration, children homes, supported accommodation, Ofsted compliance, social care consultancy, children services, care home registration, Ofsted advisory, social care training, care home software',
  authors: [{ name: 'Emerge Social Care Advisory & Consulting' }],
  openGraph: {
    title: 'Emerge Social Care - Ofsted Registration & Compliance Experts',
    description: 'End-to-end Ofsted registration and compliance support for children homes and supported accommodation providers across the UK. Building Compliance. Inspiring Quality.',
    type: 'website',
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
    description: 'Expert Ofsted registration and compliance support for children services. Building Compliance. Inspiring Quality.',
    images: ['/hero.png'],
  },
}

export default function Home() {
  return (
    <div>
      <Hero />
      <AboutSection />
      <ServicesSection />
      <WhyWorkWithUs />
      {/* <CTASection /> */}
      <Stats />
      <FAQWithSpiral />
    </div>
  )
}