/** @type {import('next').NextConfig} */
const nextConfig = {
    images: {
        remotePatterns: [
            {
                protocol: 'https',
                hostname: 'photos.imexso.com',
            },
        ],
    },
    async rewrites() {
        // 127.0.0.1: Windows često mapira "localhost" na ::1 dok artisan serve sluša samo IPv4
        const backendUrl = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000';
        return [
            {
                source: '/sanctum/:path*',
                destination: `${backendUrl}/sanctum/:path*`,
            },
            {
                source: '/_auth/:path*',
                destination: `${backendUrl}/:path*`,
            },
            {
                source: '/api/:path*',
                destination: `${backendUrl}/api/:path*`,
            },
        ];
    },
};

export default nextConfig;
