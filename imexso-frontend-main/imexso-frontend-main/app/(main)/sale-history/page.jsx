"use client";

import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import SaleHistoryList from "@/components/saleHistory/SaleHistoryList";

export default function SaleHistoryPage() {
  return (
    <>
      <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />
      <section className="boxcar-blog-section-three layout-radius py-5">
        <div className="boxcar-container">
          <SaleHistoryList />
        </div>
      </section>
      <Footer1 />
    </>
  );
}
