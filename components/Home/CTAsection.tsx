'use client'

import React from "react";
import { assets } from "../../assets/assets";
import Image from "next/image";

interface CTASectionProps {
  inView?: boolean;
}

const CTASection = ({ inView = true }: CTASectionProps) => {
  return (
    <section className="py-10">
      <div className="mx-auto">
        <div className="bg-primary pt-12">
          <div className="flex flex-col lg:flex-row-reverse gap-8 lg:gap-12 max-w-6xl mx-auto items-start">
            <div className="hidden lg:block w-full lg:w-auto flex justify-center lg:justify-end">
              <div className="space-y-8 lg:h-[300px] lg:overflow-hidden">
                <Image
                  src={assets.CTAImg} 
                  alt="Start Your Emerge Journey"
                  width={300}
                  height={400}
                  className="rounded-lg object-cover"
                />
              </div>
            </div>

            <div className="space-y-6 flex-1 px-4 sm:px-6 lg:px-0">
              <div>
                <h3 className="text-3xl sm:text-4xl lg:text-5xl font-light text-white leading-relaxed max-w-2xl">
                  Ready to Transform Your Service?
                </h3>
              </div>

              <p className="text-sm sm:text-xs leading-relaxed max-w-2xl text-white/90">
                Book your free strategy session today and let's build something exceptional together. From first idea to fully operational, Ofsted-ready service - we'll guide you every step of the way.
              </p>

              <div className="pt-4">
                <button className="bg-white text-primary px-6 py-1.5 text-xs uppercase rounded-full font-semibold transition-colors">
                  Book Free Consultation
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default CTASection;