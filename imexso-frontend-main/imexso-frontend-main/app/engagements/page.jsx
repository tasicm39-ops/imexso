"use client";

import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import EngagementsContent from "@/components/cms/EngagementsContent";

export default function EngagementsPage() {
  return (
    <>
      <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />
      <EngagementsContent />
      <Footer1 />
    </>
  );
}
