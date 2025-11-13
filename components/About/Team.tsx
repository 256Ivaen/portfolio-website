"use client";

import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { cn } from "@/lib/utils";

export interface ProfileCardProps {
  name?: string;
  title?: string;
  description?: string;
  imageUrl?: string;
  className?: string;
}

export function ProfileCard(props: ProfileCardProps) {
  const {
    name = "Tari Saikadungure",
    title = "Founder & Lead Consultant",
    description = "Tari Saikadungure is the founder of Emerge Social Care Advisory & Consulting, bringing extensive experience in children's services, Ofsted compliance, and social care leadership. With a passion for transforming care through innovation, Tari leads a team dedicated to building compliant, quality-focused services that make lasting impacts on children and families.",
    imageUrl = "/images/tari-profile.jpg",
    className,
  } = props;

  return (
    <div className={cn("w-full max-w-7xl bg-gray-300 mx-auto", className)}>
      {/* Desktop */}
      <div className='hidden md:flex relative items-center'>
        {/* Square Image */}
        <div className='w-[470px] h-[470px] rounded-xl overflow-hidden bg-gray-200 flex-shrink-0 flex items-center justify-center'>
          <Image
            src={imageUrl}
            alt={name}
            width={470}
            height={470}
            className='w-full h-full object-cover'
            draggable={false}
            priority
          />
        </div>
        {/* Overlapping Card */}
        <motion.div
          initial={{ opacity: 0, x: 20 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.5, ease: "easeOut" }}
          className='bg-primary rounded-xl shadow-2xl p-8 ml-[-80px] z-10 max-w-fit flex-1'
        >
          <div className='mb-6'>
            <h2 className='text-xl font-light uppercase text-white mb-1'>
              {name}
            </h2>

            <p className='text-xs font-medium text-white/90'>
              {title}
            </p>
          </div>

          <p className='text-white text-xs leading-relaxed mb-8'>
            {description}
          </p>

          {/* CTA Button */}
          <div className="mb-8">
            <Link 
              href="/about"
              className="bg-white text-primary px-5 py-1.5 rounded-full text-xs uppercase font-normal hover:bg-gray-100 transition-colors inline-block"
            >
              Learn More About Our Team
            </Link>
          </div>
        </motion.div>
      </div>

      {/* Mobile */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, ease: "easeOut" }}
        className='md:hidden max-w-sm mx-auto text-start bg-transparent'
      >
        {/* Square Mobile Image */}
        <div className='w-full aspect-square rounded-xl overflow-hidden mb-6 flex items-center justify-center'>
          <Image
            src={imageUrl}
            alt={name}
            width={400}
            height={400}
            className='w-full h-full object-cover'
            draggable={false}
            priority
          />
        </div>

        <div className='px-4 bg-primary rounded-xl shadow-2xl p-6'>
          <h2 className='text-xl font-light uppercase text-white mb-2'>
            {name}
          </h2>

          <p className='text-xs font-medium text-white/90 mb-4'>
            {title}
          </p>

          <p className='text-white text-xs leading-relaxed mb-6'>
            {description}
          </p>

          {/* CTA Button for Mobile */}
          <div className="mb-6">
            <Link 
              href="/about"
              className="bg-white text-primary px-5 py-1.5 rounded-full text-xs uppercase font-normal transition-colors inline-block"
            >
              Learn More About Our Team
            </Link>
          </div>
        </div>
      </motion.div>
    </div>
  );
}

// About Us Section Wrapper Component
export function AboutUsSection() {
  return (
    <div className="min-h-fit py-10 bg-white">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between mb-10">
          <div>
            <h1 className="text-3xl font-light uppercase text-start text-primary">
              About Emerge
            </h1>
            <h1 className="text-sm font-light uppercase text-start text-primary">
              Building Compliance. Inspiring Quality.
            </h1>
          </div>

          <div className="group inline-block">
            <button className="text-xs uppercase bg-primary py-2 px-5 rounded-full text-white border border-transparent transition-all duration-300 ease-in-out group-hover:bg-transparent group-hover:text-primary group-hover:border-primary">
              Our Story
            </button>
          </div>
        </div>

            <ProfileCard />
      </div>
    </div>
  );
}

export default AboutUsSection;