"use client";

import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import EquipeContent from "@/components/cms/EquipeContent";

export default function EquipePage() {
  return (
    <>
      <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />
      <EquipeContent />
      <Footer1 />
    </>
  );
}
