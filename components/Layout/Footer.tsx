"use client";

import React from "react";
import { motion } from "framer-motion";
import { useInView } from "react-intersection-observer";
import { MessageSquare, HelpCircle, Mail, MapPin, Phone } from "lucide-react";
import Link from "next/link";
import { assets } from "../../assets/assets";
import Image from "next/image";

const Footer = () => {
  const [ref, inView] = useInView({ threshold: 0.1, triggerOnce: true });

  const currentYear = new Date().getFullYear();

  const footerSections = [
    {
      title: "Services",
      links: [
        { name: "Ofsted Registration", href: "/ofsted-registration" },
        { name: "Advisory Services", href: "/advisory-services" },
        { name: "Grow Your Service", href: "/grow-your-service" },
        { name: "Training & Development", href: "/training" },
      ],
    },
    {
      title: "Solutions",
      links: [
        { name: "Software Solutions", href: "/software-solutions" },
        { name: "Workforce Solutions", href: "/workforce-solutions" },
        { name: "Regulatory Support", href: "/regulatory-support" },
        { name: "Business Support", href: "/business-support" },
      ],
    },
    {
      title: "Company",
      links: [
        { name: "How We Work", href: "/how-we-work" },
        { name: "Success Stories", href: "/success-stories" },
        { name: "Contact", href: "/contact" },
        { name: "FAQs", href: "/faqs" },
      ],
    },
  ];

  return (
    <footer className="bg-primary text-white relative overflow-hidden">
      {/* Background Elements */}
      <div className="absolute inset-0 bg-gradient-to-br from-primary/10 to-transparent"></div>
      <div className="absolute top-0 right-0 w-80 h-80 bg-primary/5 rounded-full blur-3xl"></div>
      <div className="absolute bottom-0 left-0 w-60 h-60 bg-primary/5 rounded-full blur-3xl"></div>

      {/* Main Footer Content */}
      <div className="relative z-10">
        {/* CTA Section */}
        {/* <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
          className="border-b border-secondary"
        >
          <div className="mx-auto">
            <div className="grid grid-cols-1 lg:grid-cols-2 items-center ">
              <div className="relative h-64 lg:h-full">
                <Image
                  src={assets.FooterCta}
                  alt="Background"
                  fill
                  className="object-cover"
                />
              </div>

              <div className="py-10 text-start px-4 sm:px-6 lg:px-8">
                <h3 className="text-2xl md:text-3xl font-light mb-4 ">
                  Ready to Transform Your Children's Service?
                </h3>
                <p className="text-gray-300 text-sm mb-8 max-w-2xl">
                  Join providers across the UK who have streamlined their compliance, 
                  strengthened their quality, and achieved outstanding outcomes with Emerge.
                </p>
                <div className="flex flex-col sm:flex-row gap-4 justify-start">
                  <button className="bg-secondary text-white text-xs uppercase font-light px-8 py-1.5 rounded-full hover:bg-secondary/90 transition-all duration-300 shadow-lg">
                    Get Started Today
                  </button>
                  <button className="border-2 border-secondary text-white text-xs uppercase font-normal px-8 py-1.5 rounded-full transition-all duration-300">
                    Schedule a Call
                  </button>
                </div>
              </div>
            </div>
          </div>
        </motion.div> */}

        {/* Links Section */}
        <motion.div
          ref={ref}
          className="container mx-auto px-4 sm:px-6 lg:px-8 py-12"
          initial={{ opacity: 0 }}
          animate={inView ? { opacity: 1 } : { opacity: 0 }}
          transition={{ duration: 0.6 }}
        >
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-12">
            {/* Brand Section */}
            <div className="lg:col-span-2">
              <div className="flex items-center gap-4 mb-6">
                <div className="w-16 h-16 flex items-center justify-center">
                  <Image
                    src={assets.FooterLogo}
                    alt="Emerge Social Care Logo"
                    width={40}
                    height={40}
                    className="w-16 h-16 object-contain"
                  />
                </div>
                <div>
                  <h3 className="text-sm font-light text-white uppercase">
                    EMERGE SOCIAL CARE<br />ADVISORY & CONSULTING
                  </h3>
                </div>
              </div>

              {/* Contact Info */}
              <div className="space-y-3">
                <div className="flex items-center gap-3 text-gray-300 text-sm">
                  <Mail className="w-4 h-4 text-white/60" />
                  <span className="text-xs">info@emergesocialcare.co.uk</span>
                </div>
                <div className="flex items-center gap-3 text-gray-300 text-sm">
                  <Phone className="w-4 h-4 text-white/60" />
                  <span className="text-xs">+44 (0)7508 863433</span>
                </div>
                <div className="flex items-center gap-3 text-gray-300 text-sm">
                  <MapPin className="w-4 h-4 text-white/60" />
                  <span className="text-xs">United Kingdom</span>
                </div>
              </div>
            </div>

            {/* Links Sections */}
            {footerSections.map((section, index) => (
              <motion.div
                key={section.title}
                initial={{ y: 20, opacity: 0 }}
                animate={inView ? { y: 0, opacity: 1 } : { y: 20, opacity: 0 }}
                transition={{ duration: 0.6, delay: index * 0.1 }}
              >
                <h4 className="text-white font-semibold text-sm uppercase tracking-wider mb-4">
                  {section.title}
                </h4>
                <ul className="space-y-3">
                  {section.links.map((link) => (
                    <li key={link.name}>
                      <Link
                        href={link.href}
                        className="text-gray-400 text-sm hover:text-white transition-colors duration-300 font-light"
                      >
                        {link.name}
                      </Link>
                    </li>
                  ))}
                </ul>
              </motion.div>
            ))}
          </div>
        </motion.div>

        {/* Bottom Bar */}
        <div className="border-t border-gray-800 bg-secondary backdrop-blur-sm">
          <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div className="flex flex-col md:flex-row justify-between items-center gap-4">
              <div className="text-center md:text-left">
                <p className="text-xs text-gray-400">
                  © {currentYear} Emerge Social Care Advisory & Consulting. All rights reserved.
                </p>
              </div>
              
              <div className="flex items-center gap-6">
                <div className="flex gap-4">
                  <Link
                    href="/privacy"
                    className="text-xs text-gray-400 hover:text-white transition-colors duration-300"
                  >
                    Privacy
                  </Link>
                  <Link
                    href="/terms"
                    className="text-xs text-gray-400 hover:text-white transition-colors duration-300"
                  >
                    Terms
                  </Link>
                  <Link
                    href="/compliance"
                    className="text-xs text-gray-400 hover:text-white transition-colors duration-300"
                  >
                    Compliance
                  </Link>
                </div>
                
                <div className="flex items-center gap-4">
                  <Link
                    href="/contact"
                    className="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm"
                  >
                    <MessageSquare size={14} />
                    <span className="text-xs">Contact</span>
                  </Link>
                  <Link
                    href="/faqs"
                    className="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm"
                  >
                    <HelpCircle size={14} />
                    <span className="text-xs">Help</span>
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;