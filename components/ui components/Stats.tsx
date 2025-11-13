"use client";

import * as React from "react";
import Image from "next/image";
import { assets } from "@/assets/assets";
import BrandDisplay from "./FeaturedBrands";

export default function Stats() {
  return (
    <div className="min-h-fit py-10">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between mb-10">
          <div>
            <h1 className="text-3xl font-light uppercase text-start text-primary">
              Trusted By Leaders
            </h1>
            <h1 className="text-sm font-light uppercase text-start text-primary">
              Join Services That Achieved Ofsted Compliance & Excellence
            </h1>
          </div>
        </div>
        <BrandDisplay />
      </div>
    </div>
  );
}