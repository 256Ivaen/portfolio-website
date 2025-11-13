"use client";

import React from "react";
import Image from "next/image";
import { FiBell, FiPlus } from "react-icons/fi";

interface HeroImageComponentProps {
  imageUrl: string;
}

const HeroImageComponent: React.FC<HeroImageComponentProps> = ({ imageUrl }) => {
  return (
    <div className="relative">
      <Image
        src={imageUrl}
        alt="Developer"
        width={600}
        height={600}
        className="w-[450px] h-auto object-contain"
      />
      
      {/* Floating elements with animation classes */}
      <div className="absolute top-16 -left-8 bg-white rounded-xl shadow-lg px-5 py-3 flex items-center gap-3 animate-float z-20">
        <div className="relative">
          <div className="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
            <FiBell className="w-6 h-6 text-yellow-600" />
          </div>
          <div className="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center">
            <span className="text-white text-[10px] font-bold">8</span>
          </div>
        </div>
        <span className="text-xs font-medium text-primary whitespace-nowrap">
          Job Alert Subscribe
        </span>
      </div>

      <div className="flex  items-center absolute bottom-20 -right-12 bg-white rounded-xl shadow-lg p-4 animate-float-delay z-20">
        <p className="text-xs font-medium text-primary">
          5k+ developers hired
        </p>
        <div className="flex items-center">
          {/* <div className="flex -space-x-2">
            <div className="w-8 h-8 rounded-full bg-gradient-to-br from-orange-400 to-pink-500 border-2 border-white"></div>
            <div className="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-pink-500 border-2 border-white"></div>
            <div className="w-8 h-8 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 border-2 border-white"></div>
            <div className="w-8 h-8 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 border-2 border-white"></div>
            <div className="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 border-2 border-white"></div>
          </div> */}
          <div className="w-6 h-6 rounded-full bg-primary flex items-center justify-center ml-2 cursor-pointer hover:bg-primary-dark transition-colors">
            <FiPlus className="w-4 h-4 text-white" />
          </div>
        </div>
      </div>
    </div>
  );
};

export default HeroImageComponent;