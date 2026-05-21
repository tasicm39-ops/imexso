import Header1 from "@/components/headers/Header1";
import HomePageContent from "@/components/homes/home-5/HomePageContent";
import Footer3 from "@/components/footers/Footer3";

export const metadata = {
    title: "Imexso - Find Your Perfect Car",
    description: "Find cars for sale near you",
};

export default function HomePage() {
    return (
        <>
            <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header" />
            <HomePageContent />
            <Footer3 />
        </>
    );
}
