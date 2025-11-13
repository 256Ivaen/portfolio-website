"use client";

import * as React from 'react';
import { motion } from 'framer-motion';
import { cn } from '@/lib/utils';
import { homepageServices } from '@/assets/services';
import Image from 'next/image';
import { assets } from '@/assets/assets';
import { useRouter } from 'next/navigation';

interface ServiceCardProps {
  service: {
    id: string;
    title: string;
    description: string;
    buttonText: string;
    imageUrl: any; // Changed from string to any to accept StaticImageData
  };
  index: number;
}

const ServiceCard = ({ service, index }: ServiceCardProps) => {
  const router = useRouter();

  const handleServiceClick = () => {
    // Navigate to service form with service ID
    router.push(`/contact?service=${service.id}`);
  };

  return (
    <div className="relative min-h-fit w-full overflow-hidden rounded-xl border shadow-lg bg-primary">
      <div className={cn('flex h-full w-full flex-col items-center justify-between')}>
        {/* Top Image Section with curved clip-path */}
        <motion.div 
          className="relative w-full"
          initial={{ y: -50, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ type: 'spring', duration: 0.8, delay: index * 0.2 }}
        >
          <div className="w-full h-48 relative overflow-hidden">
            <Image
              src={service.imageUrl}
              alt={service.title}
              fill
              className="object-cover"
              style={{ clipPath: 'ellipse(100% 60% at 50% 40%)' }}
            />
          </div>
        </motion.div>

        {/* Content Section */}
        <motion.div
          className="flex flex-1 flex-col justify-center space-y-6 p-8 w-full"
          initial="hidden"
          animate="visible"
          variants={{
            hidden: { opacity: 0 },
            visible: {
              opacity: 1,
              transition: {
                staggerChildren: 0.2,
                delay: index * 0.2
              },
            },
          }}
        >
          {/* Title - Left Aligned */}
          <motion.h1
            className="text-3xl font-light tracking-tight text-white text-left"
            variants={{
              hidden: { y: 20, opacity: 0 },
              visible: {
                y: 0,
                opacity: 1,
                transition: { type: 'spring', stiffness: 100, damping: 15 },
              },
            }}
          >
            {service.title}
          </motion.h1>

          {/* Description - Left Aligned with 2 line limit */}
          {/* <motion.p
            className="text-xs text-white/90 text-left leading-relaxed line-clamp-2"
            variants={{
              hidden: { y: 20, opacity: 0 },
              visible: {
                y: 0,
                opacity: 1,
                transition: { type: 'spring', stiffness: 100, damping: 15 },
              },
            }}
          >
            {service.description}
          </motion.p> */}
        </motion.div>
        
        {/* Actions Section */}
        <motion.div 
          className="w-full space-y-4 p-8 pt-0"
          initial="hidden"
          animate="visible"
          variants={{
            hidden: { opacity: 0 },
            visible: {
              opacity: 1,
              transition: {
                staggerChildren: 0.2,
                delay: index * 0.2
              },
            },
          }}
        >
          {/* Primary Button */}
          <motion.div variants={{
            hidden: { y: 20, opacity: 0 },
            visible: {
              y: 0,
              opacity: 1,
              transition: { type: 'spring', stiffness: 100, damping: 15 },
            },
          }}>
            <button 
              onClick={handleServiceClick} 
              className="w-full bg-white text-primary py-3 rounded-lg text-xs font-semibold uppercase hover:bg-gray-100 transition-colors"
            >
              {service.buttonText}
            </button>
          </motion.div>
        </motion.div>
      </div>
    </div>
  );
};

const ServicesSection = () => {
  const router = useRouter();

  const handleViewAllServices = () => {
    router.push('/services');
  };

  return (
    <div className="min-h-fit py-10 bg-white">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-10">
          <div>
            <h1 className="text-3xl font-light uppercase text-start text-primary">
              Our Services
            </h1>
            <h1 className="text-sm font-light uppercase text-start text-primary">
              Building Compliance. Inspiring Quality.
            </h1>
          </div>

          <div className="group inline-block hidden md:block">
            <button 
              onClick={handleViewAllServices}
              className="text-xs uppercase bg-primary py-2 px-5 rounded-full text-white border border-transparent transition-all duration-300 ease-in-out group-hover:bg-transparent group-hover:text-primary group-hover:border-primary"
            >
              View All Services
            </button>
          </div>
        </div>

        {/* Services Grid - 3 cards exactly */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {homepageServices.map((service, index) => (
            <ServiceCard 
              key={service.id} 
              service={service} 
              index={index} 
            />
          ))}
        </div>

        {/* Mobile CTA */}
        <div className="mt-8 text-center md:hidden">
          <button 
            onClick={handleViewAllServices}
            className="text-xs uppercase bg-primary py-2 px-5 rounded-full text-white border border-transparent transition-all duration-300 ease-in-out hover:bg-transparent hover:text-primary hover:border-primary"
          >
            View All Services
          </button>
        </div>
      </div>
    </div>
  );
};

export default ServicesSection;