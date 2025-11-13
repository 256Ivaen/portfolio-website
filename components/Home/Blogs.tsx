"use client";

import { useState } from "react";
import { BlogCard } from "../ui components/BlogCard";

const sampleBlogs = [
  {
    headline: "The Future of Tech Recruitment in 2024",
    excerpt:
      "How AI and machine learning are transforming the way companies find and hire top tech talent.",
    cover:
      "https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
    tag: "Trends",
    readingTime: 360,
    writer: "Sarah Chen",
    publishedAt: new Date("2024-01-15"),
  },
  {
    headline: "5 Essential Skills Every Developer Should Have",
    excerpt:
      "Beyond coding: The soft skills and technical competencies that make developers stand out.",
    cover:
      "https://images.unsplash.com/photo-1517077304055-6e89abbf09b0?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
    tag: "Skills",
    readingTime: 420,
    writer: "Mike Rodriguez",
    publishedAt: new Date("2024-01-10"),
  },
  {
    headline: "Remote Work: Building Distributed Engineering Teams",
    excerpt:
      "Best practices for recruiting, onboarding, and managing remote developers effectively.",
    cover:
      "https://images.unsplash.com/photo-1521737711867-e3b97375f902?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
    tag: "Remote",
    readingTime: 480,
    writer: "Emma Davis",
    publishedAt: new Date("2024-01-08"),
  },
  {
    headline: "The Art of Technical Interviews: Beyond LeetCode",
    excerpt:
      "How to design technical interviews that assess problem-solving and real-world coding abilities.",
    cover:
      "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
    tag: "Interviews",
    readingTime: 540,
    writer: "Alex Thompson",
    publishedAt: new Date("2024-01-05"),
  },
  {
    headline: "Diversity in Tech: Building Inclusive Teams",
    excerpt:
      "Strategies for creating diverse hiring processes that attract talent from all backgrounds.",
    cover:
      "https://images.unsplash.com/photo-1521791055366-0d553872125f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
    tag: "Diversity",
    readingTime: 600,
    writer: "Priya Patel",
    publishedAt: new Date("2024-01-03"),
  },
  {
    headline: "Startup vs Enterprise: Choosing Your Path",
    excerpt:
      "Understanding different career paths and work cultures in startup and enterprise environments.",
    cover:
      "https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
    tag: "Career",
    readingTime: 480,
    writer: "David Kim",
    publishedAt: new Date("2024-01-01"),
  },
  {
    headline: "The Rise of Specialized Developer Roles",
    excerpt:
      "From AI engineers to blockchain developers: Exploring emerging specialized roles and skills.",
    cover:
      "https://images.unsplash.com/photo-1555949963-aa79dcee981c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
    tag: "Specialization",
    readingTime: 420,
    writer: "Lisa Wang",
    publishedAt: new Date("2023-12-28"),
  },
  {
    headline: "Retaining Top Tech Talent: Beyond Salaries",
    excerpt:
      "How companies can create environments that keep their best developers engaged long-term.",
    cover:
      "https://images.unsplash.com/photo-1542744173-8e7e53415bb0?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
    tag: "Retention",
    readingTime: 540,
    writer: "James Wilson",
    publishedAt: new Date("2023-12-25"),
  },
];

export default function BlogsPage() {
  const [currentPage, setCurrentPage] = useState(1);
  const blogsPerPage = 3;
  const totalPages = Math.ceil(sampleBlogs.length / blogsPerPage);

  // Get current blogs for the page
  const indexOfLastBlog = currentPage * blogsPerPage;
  const indexOfFirstBlog = indexOfLastBlog - blogsPerPage;
  const currentBlogs = sampleBlogs.slice(indexOfFirstBlog, indexOfLastBlog);

  // Pagination controls
  const goToPage = (pageNumber: number) => {
    setCurrentPage(pageNumber);
  };

  const goToNextPage = () => {
    if (currentPage < totalPages) {
      setCurrentPage(currentPage + 1);
    }
  };

  const goToPrevPage = () => {
    if (currentPage > 1) {
      setCurrentPage(currentPage - 1);
    }
  };

  // Generate page numbers to show (max 5 pages)
  const getPageNumbers = () => {
    const pages = [];
    const maxVisiblePages = 5;

    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

    if (endPage - startPage + 1 < maxVisiblePages) {
      startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
      pages.push(i);
    }

    return pages;
  };

  return (
    <div className="min-h-fit py-10">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between mb-10">
          <div>
            <h1 className="text-3xl font-light uppercase text-start text-primary">
              Featured Blogs{" "}
            </h1>
            <h1 className="text-sm font-light uppercase text-start text-primary">
              Lets Get you Up to Speed
            </h1>
          </div>

          <div className="group inline-block">
            <button className="text-xs uppercase bg-primary py-2 px-5 rounded-full text-white border border-transparent transition-all duration-300 ease-in-out group-hover:bg-transparent group-hover:text-primary group-hover:border-primary">
              View More
            </button>
          </div>
        </div>

        {/* Blog Cards Grid - 3 per view with reduced gap */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
          {currentBlogs.map((blog, index) => (
            <BlogCard
              key={index}
              headline={blog.headline}
              excerpt={blog.excerpt}
              cover={blog.cover}
              tag={blog.tag}
              readingTime={blog.readingTime}
              writer={blog.writer}
              publishedAt={blog.publishedAt}
              clampLines={3}
            />
          ))}
        </div>

        {/* Pagination - Second Design */}
        <div className="flex items-center justify-center w-full">
          <div className="flex items-center justify-between w-full max-w-80 text-gray-500 font-medium">
            {/* Previous Button */}
            <button
              type="button"
              aria-label="prev"
              className="rounded-full bg-slate-200/50 hover:bg-slate-300/50 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
              onClick={goToPrevPage}
              disabled={currentPage === 1}
            >
              <svg
                width="40"
                height="40"
                viewBox="0 0 40 40"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M22.499 12.85a.9.9 0 0 1 .57.205l.067.06a.9.9 0 0 1 .06 1.206l-.06.066-5.585 5.586-.028.027.028.027 5.585 5.587a.9.9 0 0 1 .06 1.207l-.06.066a.9.9 0 0 1-1.207.06l-.066-.06-6.25-6.25a1 1 0 0 1-.158-.212l-.038-.08a.9.9 0 0 1-.03-.606l.03-.083a1 1 0 0 1 .137-.226l.06-.066 6.25-6.25a.9.9 0 0 1 .635-.263Z"
                  fill="#475569"
                  stroke="#475569"
                  strokeWidth=".078"
                />
              </svg>
            </button>

            {/* Page Numbers */}
            <div className="flex items-center gap-2 text-sm font-medium">
              {getPageNumbers().map((page) => (
                <button
                  key={page}
                  className={`h-10 w-10 flex items-center justify-center aspect-square transition-all ${
                    currentPage === page
                      ? "text-indigo-500 border border-indigo-200 rounded-full"
                      : "hover:text-gray-700"
                  }`}
                  onClick={() => goToPage(page)}
                >
                  {page}
                </button>
              ))}
            </div>

            {/* Next Button */}
            <button
              type="button"
              aria-label="next"
              className="rounded-full bg-slate-200/50 hover:bg-slate-300/50 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
              onClick={goToNextPage}
              disabled={currentPage === totalPages}
            >
              <svg
                className="rotate-180"
                width="40"
                height="40"
                viewBox="0 0 40 40"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M22.499 12.85a.9.9 0 0 1 .57.205l.067.06a.9.9 0 0 1 .06 1.206l-.06.066-5.585 5.586-.028.027.028.027 5.585 5.587a.9.9 0 0 1 .06 1.207l-.06.066a.9.9 0 0 1-1.207.06l-.066-.06-6.25-6.25a1 1 0 0 1-.158-.212l-.038-.08a.9.9 0 0 1-.03-.606l.03-.083a1 1 0 0 1 .137-.226l.06-.066 6.25-6.25a.9.9 0 0 1 .635-.263Z"
                  fill="#475569"
                  stroke="#475569"
                  strokeWidth=".078"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
