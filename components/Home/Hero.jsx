"use client";

import React, { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { assets } from "../../assets/assets";
import Image from "next/image";

const Hero = () => {
  const [currentSlide, setCurrentSlide] = useState(0);

  const slides = [
    {
      id: 1,
      title: "GROW YOUR CHILDREN'S SERVICE",
      subtitle: "From Vision to Vibrant Reality", 
      description: "Complete pathway from initial idea to sustainable operation. Service design, market analysis, and financial forecasting for lasting impact.",
      bgImage: assets.Hero1,
      cta: "Book Strategy Session"
    },
    {
      id: 2,
      title: "EMERGE SOCIAL CARE",
      subtitle: "Building Compliance. Inspiring Quality.",
      description: "Expert Ofsted registration, advisory services, and compliance support for children's homes and supported accommodation providers across the UK.",
      bgImage: assets.Hero3,
      cta: "Get Started"
    },
    {
      id: 3, 
      title: "OFSTED REGISTRATION SUPPORT",
      subtitle: "From First Idea to Fully Operational",
      description: "End-to-end Ofsted registration for Children's Homes, Supported Accommodation, and Family Assessment Units. We handle the paperwork so you can focus on care.",
      bgImage: assets.Hero2,
      cta: "Start Registration"
    },
  ];

  // Auto-advance slides
  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % slides.length);
    }, 6000);
    
    return () => clearInterval(interval);
  }, [slides.length]);

  const slideVariants = {
    enter: {
      x: "100%",
      filter: "blur(8px)",
      scale: 1.02
    },
    center: {
      x: 0,
      filter: "blur(0px)",
      scale: 1,
      transition: {
        x: { duration: 1, ease: [0.25, 0.46, 0.45, 0.94] },
        filter: { duration: 0.8, ease: "easeOut" },
        scale: { duration: 1.2, ease: "easeOut" }
      }
    },
    exit: {
      x: "-100%",
      filter: "blur(8px)",
      scale: 0.98,
      transition: {
        x: { duration: 1, ease: [0.25, 0.46, 0.45, 0.94] },
        filter: { duration: 0.6, ease: "easeIn" },
        scale: { duration: 1, ease: "easeIn" }
      }
    }
  };

  const contentVariants = {
    enter: {
      opacity: 0,
      y: 30
    },
    center: {
      opacity: 1,
      y: 0,
      transition: {
        duration: 0.8,
        ease: "easeOut",
        delay: 0.4
      }
    },
    exit: {
      opacity: 0,
      y: -30,
      transition: {
        duration: 0.5,
        ease: "easeIn"
      }
    }
  };

  return (
    <section className="h-[calc(100vh-64px)] flex items-center overflow-hidden relative">
      <AnimatePresence mode="popLayout" initial={false}>
        <motion.div
          key={currentSlide}
          className="absolute inset-0 z-0"
          variants={slideVariants}
          initial="enter"
          animate="center"
          exit="exit"
        >
          <Image
            src={slides[currentSlide].bgImage}
            alt="Emerge Social Care Background"
            fill
            className="object-cover"
            priority
          />
          <div className="absolute inset-0 bg-black/40"></div>
        </motion.div>
      </AnimatePresence>

      <div className="container mx-auto px-4 w-full relative z-10">
        <div className="flex flex-col items-start text-left max-w-3xl">
          <AnimatePresence mode="wait">
            <motion.div
              key={currentSlide}
              className="space-y-4 w-full"
              variants={contentVariants}
              initial="enter"
              animate="center"
              exit="exit"
            >
              <div>
                <motion.h1 className="text-3xl md:text-6xl font-light text-white leading-tight">
                  {slides[currentSlide].title}
                </motion.h1>
                <motion.h2 className="text-lg md:text-xl font-light text-white/90 mt-2">
                  {slides[currentSlide].subtitle}
                </motion.h2>
              </div>

              <motion.p className="text-xs text-white leading-relaxed">
                {slides[currentSlide].description}
              </motion.p>

              <motion.button className="bg-primary hover:bg-primary-dark text-white px-8 py-3 rounded-full text-sm uppercase font-medium transition-colors w-full sm:w-auto max-w-xs">
                {slides[currentSlide].cta}
              </motion.button>
            </motion.div>
          </AnimatePresence>
        </div>
      </div>
    </section>
  );
};

export default Hero;