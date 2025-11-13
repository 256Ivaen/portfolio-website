"use client";

import React, { useState, useEffect, useRef } from "react";
import { usePathname, useRouter } from "next/navigation";
import { Menu, X } from "lucide-react";
import { assets } from "../../assets/assets";
import Image from "next/image";

const Navbar = () => {
  const [menuOpen, setMenuOpen] = useState(false);
  const pathname = usePathname();
  const router = useRouter();
  const mobileMenuRef = useRef<HTMLDivElement>(null);

  const navItems = [
    { text: "Home", path: "/" },
    { text: "Services", path: "/services" },
    { text: "Training", path: "/training" },
    { text: "Software", path: "/software" },
    { text: "How We Work", path: "/how-we-work" },
  ];

  const isActive = (path: string) => {
    if (path === "/") {
      return pathname === path;
    }
    return pathname.startsWith(path);
  };

  const handleNavigation = (path: string) => {
    setMenuOpen(false);
    router.push(path);
  };

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        mobileMenuRef.current &&
        !mobileMenuRef.current.contains(event.target as Node)
      ) {
        setMenuOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  useEffect(() => {
    if (menuOpen) {
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = "unset";
    }

    return () => {
      document.body.style.overflow = "unset";
    };
  }, [menuOpen]);

  return (
    <>
      <header className="fixed top-0 w-full z-50 bg-white shadow-sm">
        <div className="h-1 bg-primary" />

        <div className="w-full bg-white">
          <div className="container mx-auto px-4 md:px-6 lg:px-8">
            <div className="flex items-center justify-between h-16 md:h-20">
              <div
                className="flex items-center cursor-pointer gap-2"
                onClick={() => handleNavigation("/")}
              >
                <Image
                  src={assets.logo}
                  alt="Emerge Social Care Logo"
                  width={40}
                  height={40}
                  className="h-10 w-auto"
                />
              </div>

              <div className="flex items-center space-x-6">
                <nav className="hidden lg:flex items-center space-x-6">
                  <ul className="flex items-center space-x-1">
                    {navItems.map((item, index) => (
                      <li key={index}>
                        <button
                          onClick={() => handleNavigation(item.path)}
                          className={`relative flex items-center text-sm px-4 rounded-md font-light transition-all duration-300
                              ${
                                isActive(item.path)
                                  ? "text-primary uppercase font-semibold after:content-[''] after:absolute after:left-1/2 after:-translate-x-1/2 after:-bottom-[2px] after:h-[2px] after:w-[20%] after:bg-primary after:rounded-full"
                                  : "text-primary uppercase hover:text-primary/80"
                              }`}
                          aria-current={
                            isActive(item.path) ? "page" : undefined
                          }
                        >
                          <span>{item.text}</span>
                        </button>
                      </li>
                    ))}
                  </ul>
                </nav>

                <div className="hidden lg:block">
                  <button className="w-full uppercase border-2 border-primary rounded-full text-primary text-xs font-light px-5 py-2 flex items-center justify-center hover:bg-primary hover:text-white transition-all duration-300">
                    Contact Us
                  </button>
                </div>

                <button
                  className="lg:hidden text-primary hover:text-primary/80 focus:outline-none bg-white rounded-full p-2 border border-gray-200"
                  onClick={() => setMenuOpen(!menuOpen)}
                  aria-expanded={menuOpen}
                  aria-controls="mobile-menu"
                  aria-label={menuOpen ? "Close menu" : "Open menu"}
                >
                  {menuOpen ? (
                    <X className="h-5 w-5" />
                  ) : (
                    <Menu className="h-5 w-5" />
                  )}
                </button>
              </div>
            </div>
          </div>

          {menuOpen && (
            <>
              <div 
                className="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 lg:hidden mt-16"
                onClick={() => setMenuOpen(false)}
              />
              
              <div
                ref={mobileMenuRef}
                id="mobile-menu"
                className="absolute top-full left-0 right-0 bg-white shadow-xl z-50 lg:hidden border-t border-gray-200"
              >
                <div className="container mx-auto px-4 py-6">
                  <div className="space-y-2 mb-8">
                    {navItems.map((item, index) => (
                      <button
                        key={index}
                        onClick={() => handleNavigation(item.path)}
                        className={`block text-sm px-4 py-3 rounded-lg font-medium w-full text-left transition-all duration-300 ${
                          isActive(item.path)
                            ? "text-primary bg-primary/10 border-l-4 border-primary"
                            : "text-gray-700 hover:text-primary hover:bg-gray-50"
                        }`}
                        aria-current={isActive(item.path) ? "page" : undefined}
                      >
                        {item.text}
                      </button>
                    ))}
                  </div>

                  <div className="mb-8">
                    <button className="w-full bg-primary uppercase text-white text-sm font-medium rounded-lg px-4 py-3 flex items-center justify-center hover:bg-primary/90 transition-all duration-300">
                      Contact Us
                    </button>
                  </div>

                  <div className="flex justify-center">
                    <button
                      onClick={() => setMenuOpen(false)}
                      className="text-gray-500 hover:text-primary transition-colors p-3 rounded-full border-2 border-gray-300 hover:border-primary flex items-center justify-center"
                      aria-label="Close menu"
                    >
                      <X className="h-5 w-5" />
                    </button>
                  </div>
                </div>
              </div>
            </>
          )}
        </div>
      </header>

      <style jsx global>{`
        #mobile-menu {
          animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
          from {
            opacity: 0;
            transform: translateY(-10px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }

        body {
          overflow: ${menuOpen ? 'hidden' : 'unset'};
        }
      `}</style>
    </>
  );
};

export default Navbar;