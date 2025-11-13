"use client";

import React, { useState } from "react";
import { ChevronDown, PhoneCall } from "lucide-react";

export default function FAQPage() {
  const [openItems, setOpenItems] = useState<number[]>([]);

  const faqs = [
    {
      q: "How long does the Ofsted registration process take with Emerge?",
      a: "Typically 3-6 months from start to registration. We streamline the process by handling all documentation, premises compliance, and interview preparation simultaneously. Our structured approach reduces delays and ensures you meet all regulatory requirements efficiently."
    },
    {
      q: "What's included in your Ofsted registration support?",
      a: "Our end-to-end support includes: SC1/SC2 form completion, policy & procedure development, premises compliance checks, location risk assessments, Responsible Individual preparation, financial forecasting, and mock Ofsted interviews. We manage the entire journey so you can focus on your vision."
    },
    {
      q: "Do you help existing providers improve their Ofsted ratings?",
      a: "Yes, we specialize in moving services from 'Requires Improvement' to 'Good' and 'Outstanding'. Our advisory services include mock inspections, quality audits, staff training, and improvement plans. We've helped numerous providers achieve higher ratings within one inspection cycle."
    },
    {
      q: "What are your success rates with Ofsted registrations?",
      a: "We maintain a 98% success rate for first-time Ofsted registrations. Our comprehensive approach ensures all regulatory requirements are met before submission, significantly reducing the risk of rejection or delays."
    },
  ];

  const toggleItem = (index: number) => {
    setOpenItems(prev => 
      prev.includes(index) 
        ? prev.filter(item => item !== index)
        : [...prev, index]
    );
  };

  return (
    <div className="min-h-fit py-10 bg-white">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between mb-10">
          <div>
            <h1 className="text-3xl font-light uppercase text-start text-primary">
              Frequently Asked Questions{" "}
            </h1>
            <h1 className="text-sm font-light uppercase text-start text-primary">
              Everything About Ofsted Registration & Compliance
            </h1>
          </div>

          <div className="group inline-block">
            <button className="text-xs uppercase bg-primary py-2 px-5 rounded-full text-white border border-transparent transition-all duration-300 ease-in-out group-hover:bg-transparent group-hover:text-primary group-hover:border-primary">
              Contact Support
            </button>
          </div>
        </div>

        <div className="grid lg:grid-cols-2 gap-12 items-center">
          <div className="flex gap-10 flex-col">
            <div className="flex gap-4 flex-col">
              <div className="flex gap-2 flex-col">
                <h4 className="text-3xl md:text-4xl tracking-tighter max-w-xl text-left font-regular text-gray-900">
                  Your Ofsted registration questions answered
                </h4>
                <p className="text-xs max-w-xl lg:max-w-lg leading-relaxed tracking-tight text-gray-600 text-left">
                  Get clear answers about our Ofsted registration process, compliance support, 
                  and how we help children's service providers achieve and maintain regulatory excellence.
                </p>
              </div>
            </div>
            
            <div className="flex flex-col gap-4">
              <div className="flex items-center gap-4">
                <div className="w-2 h-2 bg-primary rounded-full"></div>
                <span className="text-xs text-gray-600">98% Ofsted registration success rate</span>
              </div>
              <div className="flex items-center gap-4">
                <div className="w-2 h-2 bg-primary rounded-full"></div>
                <span className="text-xs text-gray-600">100+ children's services supported</span>
              </div>
              <div className="flex items-center gap-4">
                <div className="w-2 h-2 bg-primary rounded-full"></div>
                <span className="text-xs text-gray-600">Expert guidance from registration to Outstanding</span>
              </div>
            </div>
          </div>

          <div className="w-full">
            <div className="space-y-0">
              {faqs.map((faq, index) => (
                <div 
                  key={index}
                  className="border-b border-gray-200 last:border-b-0 transition-colors hover:border-primary"
                >
                  <button
                    onClick={() => toggleItem(index)}
                    className="flex items-center justify-between w-full py-6 px-6 text-left hover:bg-gray-50/50 transition-colors"
                  >
                    <span className="text-xs font-semibold text-gray-900 pr-4 leading-relaxed">
                      {faq.q}
                    </span>
                    <ChevronDown 
                      className={`w-4 h-4 text-gray-500 transition-transform duration-300 ${
                        openItems.includes(index) ? "rotate-180" : ""
                      }`} 
                    />
                  </button>
                  
                  <div 
                    className={`transition-all duration-300 ease-in-out overflow-hidden ${
                      openItems.includes(index) 
                        ? "max-h-96 opacity-100 pb-6" 
                        : "max-h-0 opacity-0"
                    }`}
                  >
                    <div className="px-6">
                      <p className="text-xs text-gray-600 leading-relaxed">
                        {faq.a}
                      </p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}