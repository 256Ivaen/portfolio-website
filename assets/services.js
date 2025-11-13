import { assets } from "./assets";

export const services = {
    ofstedRegistration: {
      id: "ofsted-registration",
      title: "Ofsted Registration Support",
      description: "End-to-end Ofsted registration support for Children's Homes, Supported Accommodation, and Family Assessment Units. We manage every step from documentation to inspection readiness.",
      buttonText: "Start Registration",
      imageUrl: assets.Hero2 // Using your existing asset
    },
    growYourService: {
      id: "grow-your-service",
      title: "Grow Your Children's Service",
      description: "Structured pathway supporting providers from first idea to fully operational, Ofsted-ready service with sustainable operation.",
      buttonText: "Start Growth Journey", 
      imageUrl: assets.Hero1 // Using your existing asset
    },
    advisoryServices: {
      id: "advisory-services",
      title: "Advisory Services",
      description: "Bespoke advisory solutions to strengthen leadership, governance, and compliance across children's residential and supported accommodation services.",
      buttonText: "Get Advisory Support",
      imageUrl: assets.Hero3 // Using your existing asset
    },
    trainingDevelopment: {
      id: "training-development",
      title: "Training & Development",
      description: "Specialized training programmes ensuring your workforce is confident, compliant, and inspection-ready.",
      buttonText: "Explore Training",
      imageUrl: assets.Hero1
    },
    regulatorySolutions: {
      id: "regulatory-solutions",
      title: "Regulatory Solutions",
      description: "Professional Reg 44 & Reg 25 services ensuring continuous improvement and transparency across your provisions.",
      buttonText: "View Regulatory Support",
      imageUrl: assets.Hero2
    },
    whistleblowingComplaints: {
      id: "whistleblowing-complaints",
      title: "Whistleblowing & Complaints",
      description: "External whistleblowing and complaints services providing secure, impartial routes for concerns to be raised and addressed.",
      buttonText: "Learn More",
      imageUrl: assets.Hero3
    }
  };
  
  export const allServices = Object.values(services);
  export const homepageServices = [services.ofstedRegistration, services.growYourService, services.advisoryServices];