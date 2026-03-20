/** @type {import('next').NextConfig} */
const nextConfig = {
  output: "export",
  trailingSlash: true,

  images: {
    unoptimized: true,

    remotePatterns: [
      {
        protocol: "https",
        hostname: "lh3.googleusercontent.com",
      },
      {
        protocol: "https",
        hostname: "gray.ninjamarketing360.com",
        pathname: "/admin/uploads/**",
      },
    ],
  },
};

module.exports = nextConfig;