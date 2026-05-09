import Header1 from "@/components/headers/Header1";
import Hero from "@/components/homes/home-5/Hero";
import Brands from "@/components/homes/home-5/Brands";
import Cta from "@/components/common/Cta";
import Cars from "@/components/homes/home-5/Cars";
import Features from "@/components/homes/home-5/Features";
import Cars2 from "@/components/homes/home-5/Cars2";
import Brands2 from "@/components/homes/home-5/Brands2";
import Footer3 from "@/components/footers/Footer3";

export const metadata = {
    title: "Imexso - Find Your Perfect Car",
    description: "Find cars for sale near you",
};

export default function HomePage() {
    return (
        <>
            <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header" />
            <Hero />
            <Brands />
            <Cta />
            <Cars />
            <Features />
            <Cars2 />
            <Brands2 />
            <Footer3 />
        </>
    );
}
