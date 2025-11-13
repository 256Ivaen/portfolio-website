"use client";

import Image from "next/image";

export interface BlogCardProps {
  headline: string;
  excerpt: string;
  cover?: string;
  tag?: string;
  readingTime?: number; // in seconds
  writer?: string;
  publishedAt?: Date;
  clampLines?: number;
}

// Human-friendly read time: seconds -> "X min read"
export function formatReadTime(seconds: number): string {
  if (!seconds || seconds < 60) return "Less than 1 min read";
  const minutes = Math.ceil(seconds / 60);
  return `${minutes} min read`;
}

// Date -> "Aug 15, 2025" (localized but concise)
export function formatPostDate(date: Date): string {
  if (!date) return "";
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

export const BlogCard: React.FC<BlogCardProps> = ({
  cover,
  tag,
  readingTime,
  headline,
  excerpt,
  writer,
  publishedAt,
  clampLines,
}) => {
  const hasMeta = tag || readingTime;
  const hasFooter = writer || publishedAt;

  // Simple clamp styles without cn - FIXED TypeScript issue
  const clampStyle = clampLines && clampLines > 0 ? {
    display: '-webkit-box',
    WebkitLineClamp: clampLines,
    WebkitBoxOrient: 'vertical' as const,
    overflow: 'hidden',
    textOverflow: 'ellipsis'
  } : {};

  return (
    <div className="flex w-full flex-col gap-2 overflow-hidden rounded-2xl bg-primary p-3 shadow-lg">
      {cover && (
        <div className="p-0">
          <div className="relative h-40 w-full">
            <Image
              src={cover}
              alt={headline}
              fill
              className="rounded-xl object-cover"
            />
          </div>
        </div>
      )}

      <div className="flex-grow p-2">
        {hasMeta && (
          <div className="mb-3 flex items-center text-xs text-white/80">
            {tag && (
              <span className="rounded-full bg-white/20 px-2 py-1 text-xs text-white">
                {tag}
              </span>
            )}
            {tag && readingTime && <span className="mx-1">•</span>}
            {readingTime && <span>{formatReadTime(readingTime)}</span>}
          </div>
        )}

        <h2 className="mb-2 text-md font-bold leading-tight text-white">
          {headline}
        </h2>

        <p
          className="text-xs text-white/90"
          style={clampStyle}
        >
          {excerpt}
        </p>
      </div>

      {hasFooter && (
        <div className="flex items-center justify-between p-2">
          {writer && (
            <div>
              <p className="text-xs text-white/80">By</p>
              <p className="text-sm font-semibold text-white">{writer}</p>
            </div>
          )}
          {publishedAt && (
            <div className={writer ? "text-right" : ""}>
              <p className="text-xs text-white/80">Published</p>
              <p className="text-sm font-semibold text-white">
                {formatPostDate(publishedAt)}
              </p>
            </div>
          )}
        </div>
      )}
    </div>
  );
};