"use client";

import { useLocale } from "@/context/LocaleContext";
import { cmsImage } from "@/lib/cmsPaths";
import CmsPageShell from "./CmsPageShell";
import CmsSectionHeader from "./CmsSectionHeader";
import CmsBandTitle from "./CmsBandTitle";

export default function EquipeContent() {
  const { t } = useLocale();

  return (
    <CmsPageShell titleKey="pages.meta.equipeTitle">
      <section className="py-4">
        <div className="boxcar-container text-center">
          <h1 className="fw-bold mb-0">{t("pages.equipe.hero")}</h1>
        </div>
      </section>

      <section>
        <CmsSectionHeader>{t("pages.equipe.direction")}</CmsSectionHeader>
        <div className="cms-section-body pb-4">
          <div className="boxcar-container py-3">
            <div className="row g-3 cms-img-grid">
              <div className="col-md-3 text-center">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("Imexso_0121.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                />
                <p
                  className="mt-2 mb-0 py-2 small fw-semibold cms-caption-bar"
                >
                  Philippe Portal
                </p>
              </div>
              <div className="col-md-3">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("_CD29530.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                  style={{ minHeight: 200, objectFit: "cover" }}
                />
              </div>
              <div className="col-md-6">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("parc_team.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                  style={{ minHeight: 280, objectFit: "cover" }}
                />
              </div>
            </div>
          </div>
        </div>
      </section>

      <section>
        <CmsSectionHeader>{t("pages.equipe.commercial")}</CmsSectionHeader>
        <div className="cms-section-body pb-4">
          <div className="boxcar-container py-3">
            <div className="row g-3">
              {[
                {
                  img: "Imexso_0589.jpg",
                  name: "Steven",
                  phone: "+32 (0)2 365 08 21",
                },
                {
                  img: "Imexso_0343.jpg",
                  name: "Klaas",
                  phone: "+32 (0)2 361 52 59",
                },
                {
                  img: "Imexso_0249.jpg",
                  name: "Waleed",
                  phone: "+32 (0)2 365 08 79",
                },
                {
                  img: "_CD29530.jpg",
                  name: null,
                  wide: true,
                },
              ].map((row, i) =>
                row.wide ? (
                  <div key={i} className="col-md-3">
                    {/* eslint-disable-next-line @next/next/no-img-element */}
                    <img
                      src={cmsImage(row.img)}
                      alt=""
                      className="w-100 rounded-1"
                      style={{ minHeight: 220, objectFit: "cover" }}
                    />
                  </div>
                ) : (
                  <div key={i} className="col-md-3 text-center">
                    {/* eslint-disable-next-line @next/next/no-img-element */}
                    <img
                      src={cmsImage(row.img)}
                      alt=""
                      className="w-100 rounded-1"
                    />
                    <p className="mt-2 mb-0 py-2 small fw-semibold cms-caption-bar">
                      {row.name}
                    </p>
                    <p
                      className="mb-0 py-1 small fw-semibold cms-caption-bar"
                    >
                      {row.phone}
                    </p>
                  </div>
                ),
              )}
            </div>
          </div>
        </div>
      </section>

      <section>
        <CmsSectionHeader>{t("pages.equipe.prepTitle")}</CmsSectionHeader>
        <CmsBandTitle>{t("pages.equipe.prepSubtitle")}</CmsBandTitle>
        <div className="cms-section-body">
          <div className="boxcar-container py-3">
            <div className="row g-3 mb-3">
              <div className="col-md-3">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("Imexso_2552.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                  style={{ minHeight: 200, objectFit: "cover" }}
                />
              </div>
              <div className="col-md-6">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("_CD29782.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                  style={{ minHeight: 200, objectFit: "cover" }}
                />
              </div>
              <div className="col-md-3 text-center">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("mika.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                />
                <p
                  className="mt-2 mb-0 py-3 small fw-semibold cms-caption-bar"
                >
                  Michael
                </p>
              </div>
            </div>
            <div className="row g-3 pb-4">
              <div className="col-md-3 text-center">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("pascal.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                />
                <p
                  className="mt-2 mb-0 py-3 small fw-semibold cms-caption-bar"
                >
                  Pascal
                </p>
              </div>
              <div className="col-md-6">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("Imexso_2527.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                  style={{ minHeight: 220, objectFit: "cover" }}
                />
              </div>
              <div className="col-md-3">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("_CD29585.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                  style={{ minHeight: 220, objectFit: "cover" }}
                />
              </div>
            </div>
          </div>
        </div>
      </section>

      <section>
        <CmsSectionHeader>{t("pages.equipe.logistique")}</CmsSectionHeader>
        <div className="cms-section-body pb-4">
          <div className="boxcar-container py-3">
            <div className="row g-3">
              <div className="col-md-3">
                <div className="row g-2">
                  <div className="col-12">
                    {/* eslint-disable-next-line @next/next/no-img-element */}
                    <img
                      src={cmsImage("mar.jpg")}
                      alt=""
                      className="w-100 rounded-1"
                    />
                  </div>
                  <div className="col-12 text-center">
                    <p
                      className="mb-1 py-2 small fw-semibold cms-caption-bar"
                    >
                      Marilyn
                    </p>
                    <p
                      className="mb-0 py-1 small fw-semibold cms-caption-bar"
                    >
                      +32 (0)2 365 08 77
                    </p>
                  </div>
                </div>
              </div>
              <div className="col-md-3">
                <div className="row g-2">
                  <div className="col-12">
                    {/* eslint-disable-next-line @next/next/no-img-element */}
                    <img
                      src={cmsImage("sara.jpg")}
                      alt=""
                      className="w-100 rounded-1"
                    />
                  </div>
                  <div className="col-12 text-center">
                    <p
                      className="mb-1 py-2 small fw-semibold cms-caption-bar"
                    >
                      Sara
                    </p>
                    <p
                      className="mb-0 py-1 small fw-semibold cms-caption-bar"
                    >
                      +32 (0)2 365 08 14
                    </p>
                  </div>
                </div>
              </div>
              <div className="col-md-3">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("parc4.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                  style={{ minHeight: 280, objectFit: "cover" }}
                />
              </div>
              <div className="col-md-3">
                <div className="row g-2">
                  <div className="col-12">
                    {/* eslint-disable-next-line @next/next/no-img-element */}
                    <img
                      src={cmsImage("Imexso_2605.jpg")}
                      alt=""
                      className="w-100 rounded-1"
                    />
                  </div>
                  <div className="col-12 text-center">
                    <p
                      className="mb-1 py-2 small fw-semibold cms-caption-bar"
                    >
                      Frederic
                    </p>
                    <p
                      className="mb-0 py-1 small fw-semibold cms-caption-bar"
                    >
                      Vincent
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section>
        <CmsSectionHeader>{t("pages.equipe.admin")}</CmsSectionHeader>
        <div className="cms-section-body pb-4">
          <div className="boxcar-container py-3">
            <div className="row g-3">
              <div className="col-md-3">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("documents.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                  style={{ minHeight: 200, objectFit: "cover" }}
                />
              </div>
              <div className="col-md-3 text-center">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("Imexso_1702.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                />
                <p
                  className="mt-2 mb-0 py-2 small fw-semibold cms-caption-bar"
                >
                  Fabienne
                </p>
              </div>
              <div className="col-md-3 text-center">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("Imexso_1476.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                />
                <p
                  className="mt-2 mb-0 py-2 small fw-semibold cms-caption-bar"
                >
                  Céline
                </p>
              </div>
              <div className="col-md-3">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("documents.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                  style={{ minHeight: 200, objectFit: "cover" }}
                />
              </div>
            </div>
          </div>
        </div>
      </section>

      <section>
        <CmsSectionHeader>{t("pages.equipe.compta")}</CmsSectionHeader>
        <div className="cms-section-body pb-4">
          <div className="boxcar-container py-3">
            <div className="row g-3 align-items-stretch">
              <div className="col-md-3 text-center">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("Imexso_1880.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                />
                <p
                  className="mt-2 mb-0 py-2 small fw-semibold cms-caption-bar"
                >
                  Julie
                </p>
              </div>
              <div className="col-md-6">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("office.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                  style={{ minHeight: 260, objectFit: "cover" }}
                />
              </div>
              <div className="col-md-3">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("DJI_0046.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                  style={{ minHeight: 260, objectFit: "cover" }}
                />
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="pb-5">
        <CmsSectionHeader>{t("pages.equipe.info")}</CmsSectionHeader>
        <div className="cms-section-body">
          <div className="boxcar-container py-3">
            <div className="row g-3">
              <div className="col-md-3 text-center">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("Imexso_2176.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                />
                <p
                  className="mt-2 mb-0 py-2 small fw-semibold cms-caption-bar"
                >
                  Fabio
                </p>
              </div>
              <div className="col-md-3 text-center">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("Imexso_2106.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                />
                <p
                  className="mt-2 mb-0 py-2 small fw-semibold cms-caption-bar"
                >
                  Adrian
                </p>
              </div>
              <div className="col-md-6">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("info.jpg")}
                  alt=""
                  className="w-100 rounded-1"
                  style={{ minHeight: 240, objectFit: "cover" }}
                />
              </div>
            </div>
          </div>
        </div>
      </section>
    </CmsPageShell>
  );
}
