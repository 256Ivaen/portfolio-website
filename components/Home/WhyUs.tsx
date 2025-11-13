"use client";

import React, { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import Image from "next/image";
import { assets } from "../../assets/assets";
import { ChevronLeft, ChevronRight } from "lucide-react";

const WhyChooseUs = () => {
  const [activeCard, setActiveCard] = useState(0);
  const [isHovered, setIsHovered] = useState(false);

  const cards = [
    {
      id: 1,
      title: "Why Choose Emerge",
      subtitle: "Hands-On Expertise",
      description: "We've worked in, managed, and registered multiple Ofsted services with proven success helping homes move from Requires Improvement to Good and Outstanding ratings.",
      imageSrc: assets.Hero1,
      imageAlt: "Hands-On Expertise"
    },
    {
      id: 2,
      title: "Why Choose Emerge", 
      subtitle: "Total Guidance",
      description: "From form-filling to leadership coaching—we do the heavy lifting so you can focus on your vision. No templates or generic packs—every plan is customised to your service.",
      imageSrc: assets.Hero2,
      imageAlt: "Total Guidance"
    },
    {
      id: 3,
      title: "Why Choose Emerge",
      subtitle: "Regulatory Confidence",
      description: "We prepare you for interviews, inspections, and full compliance. We speak both the language of care and the language of business—you'll get operational rigour and practice integrity.",
      imageSrc: assets.Hero3,
      imageAlt: "Regulatory Confidence"
    },
    {
      id: 4,
      title: "Why Choose Emerge",
      subtitle: "End-to-End Partnership",
      description: "From the first form to the first inspection—we walk with you every step of the way. Our support is designed to be simple, clear, and action-oriented—no jargon, no buried complexity, just real progress.",
      imageSrc: assets.Hero1,
      imageAlt: "End-to-End Partnership"
    }
  ];

  // Auto-advance cards every 5 seconds
  useEffect(() => {
    if (isHovered) return;

    const interval = setInterval(() => {
      setActiveCard((prev) => (prev + 1) % cards.length);
    }, 5000);
    
    return () => clearInterval(interval);
  }, [cards.length, isHovered]);

  const nextCard = () => {
    setActiveCard((prev) => (prev + 1) % cards.length);
  };

  const prevCard = () => {
    setActiveCard((prev) => (prev - 1 + cards.length) % cards.length);
  };

  const slideVariants = {
    enter: {
      x: 100,
      opacity: 0,
    },
    center: {
      x: 0,
      opacity: 1,
    },
    exit: {
      x: -100,
      opacity: 0,
    }
  };

  const contentVariants = {
    enter: {
      opacity: 0,
      y: 20
    },
    center: {
      opacity: 1,
      y: 0,
    },
    exit: {
      opacity: 0,
      y: -20,
    }
  };

  return (
    <div className="min-h-fit py-10 bg-white">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-10">
          <div>
            <h1 className="text-3xl font-light uppercase text-start text-primary">
              Why Choose Emerge
            </h1>
            <h1 className="text-sm font-light uppercase text-start text-primary">
              Building Compliance. Inspiring Quality.
            </h1>
          </div>
        </div>

        {/* Card Section */}
        <div 
          className="w-full max-w-7xl mx-auto"
          onMouseEnter={() => setIsHovered(true)}
          onMouseLeave={() => setIsHovered(false)}
        >
          <div className="overflow-hidden rounded-xl bg-primary text-white flex flex-col md:flex-row">
            {/* Image Section - Rectangular */}
            <div className="md:w-1/3 w-full relative overflow-hidden order-2 md:order-1">
              <AnimatePresence mode="popLayout" initial={false}>
                <motion.div
                  key={activeCard}
                  className="w-full h-full"
                  variants={slideVariants}
                  initial="enter"
                  animate="center"
                  exit="exit"
                  transition={{
                    duration: 0.6,
                    ease: "easeInOut"
                  }}
                >
                  <div className="w-full h-48 md:h-full relative">
                    <Image
                      src={cards[activeCard].imageSrc}
                      alt={cards[activeCard].imageAlt}
                      fill
                      className="object-cover"
                      sizes="(max-width: 768px) 100vw, 33vw"
                    />
                  </div>
                </motion.div>
              </AnimatePresence>
            </div>

            {/* Content Section */}
            <div className="md:w-2/3 w-full p-6 md:p-8 flex flex-col justify-center relative order-1 md:order-2">
              <AnimatePresence mode="wait">
                <motion.div
                  key={activeCard}
                  variants={contentVariants}
                  initial="enter"
                  animate="center"
                  exit="exit"
                  transition={{
                    duration: 0.6,
                    ease: "easeOut",
                    delay: 0.3
                  }}
                >
                  <div>
                    <p className="text-xs font-semibold text-white/90">{cards[activeCard].title}</p>
                    <h2 className="mt-1 text-2xl md:text-3xl font-light tracking-tight text-white">
                      {cards[activeCard].subtitle}
                    </h2>
                    <p className="mt-4 text-white/90 text-xs leading-relaxed">
                      {cards[activeCard].description}
                    </p>
                  </div>
                </motion.div>
              </AnimatePresence>

              {/* Arrow Navigation */}
              <div className="flex space-x-4 mt-8">
                <button
                  onClick={prevCard}
                  className="w-10 h-10 bg-white text-primary rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors"
                  aria-label="Previous reason"
                >
                  <ChevronLeft className="w-5 h-5" />
                </button>
                <button
                  onClick={nextCard}
                  className="w-10 h-10 bg-white text-primary rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors"
                  aria-label="Next reason"
                >
                  <ChevronRight className="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default WhyChooseUs;