"use client";

import { useLocale } from "@/context/LocaleContext";
import { cmsImage } from "@/lib/cmsPaths";
import CmsPageShell from "./CmsPageShell";
import CmsSectionHeader from "./CmsSectionHeader";

function T({ k }) {
  const { t } = useLocale();
  return <>{t(`pages.historique.${k}`)}</>;
}

export default function HistoriqueContent() {
  return (
    <CmsPageShell titleKey="pages.meta.historiqueTitle">
      <section className="py-4">
        <div className="boxcar-container text-center">
          <h1 className="fw-bold cms-h1-lead">
            <T k="dic_histoire_Slogan" />
          </h1>
        </div>
      </section>

      <section className="pb-5 mb-4">
        <div className="boxcar-container">
          <div className="p-4 p-lg-5 position-relative cms-bg-surface cms-surface-card">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              src={cmsImage("mini.png")}
              alt=""
              className="historique-feat-img"
            />
            <div className="row">
              <div className="col-md-6">
                <p className="mb-3">
                  <T k="dic_histoire_paragraphe_1" />
                </p>
                <p className="mb-3">
                  <T k="dic_histoire_paragraphe_2" />
                </p>
                <p className="mb-3">
                  <T k="dic_histoire_paragraphe_3" />
                </p>
              </div>
              <div className="col-md-6">
                <p className="mb-3">
                  <T k="dic_histoire_paragraphe_4" />
                </p>
                <p className="mb-3">
                  <T k="dic_histoire_paragraphe_5" />
                </p>
                <p className="mb-3">
                  <T k="dic_histoire_paragraphe_6" />
                </p>
                <p className="mb-3">
                  <T k="dic_histoire_paragraphe_7" />
                </p>
              </div>
            </div>
            <div className="text-center mt-4">
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img
                src={cmsImage("oldimexso.jpg")}
                alt=""
                style={{ maxWidth: "min(500px, 100%)" }}
                className="img-fluid"
              />
            </div>
          </div>
        </div>
      </section>

      <section>
        <CmsSectionHeader>
          <T k="dic_histoire_CROISSANCE_PERMANENTE" />
        </CmsSectionHeader>
        <div className="cms-bg-tint-outer pb-4">
          <div className="boxcar-container">
            <div className="p-4 p-lg-5 cms-bg-surface cms-surface-card">
              <div className="row">
                <div className="col-md-6">
                  <p className="mb-3">
                    <T k="dic_histoire_paragraphe_8" />
                  </p>
                  <p className="mb-3">
                    <T k="dic_histoire_paragraphe_9" />
                  </p>
                  <p className="mb-3">
                    <T k="dic_histoire_paragraphe_10" />
                  </p>
                  <p className="mb-3">
                    <T k="dic_histoire_paragraphe_11" />
                  </p>
                </div>
                <div className="col-md-6">
                  <p className="mb-3">
                    <T k="dic_histoire_paragraphe_12" />
                  </p>
                  <p className="mb-3">
                    <T k="dic_histoire_paragraphe_13" />
                  </p>
                  <p className="mb-3">
                    <T k="dic_histoire_paragraphe_14" />
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="pb-5">
        <CmsSectionHeader>
          <T k="dic_histoire_IMEXSO_FETE_30_ANS" />
        </CmsSectionHeader>
        <div className="cms-section-body pb-4">
          <div className="boxcar-container py-3">
            <div className="row g-3">
              {["30-5.jpg", "30-6.jpg", "30-1.jpg", "30-4.jpg", "30-3.jpg", "30-2.jpg"].map(
                (name) => (
                  <div key={name} className="col-md-4">
                    {/* eslint-disable-next-line @next/next/no-img-element */}
                    <img
                      src={cmsImage(name)}
                      alt=""
                      className="w-100 rounded-1"
                    />
                  </div>
                ),
              )}
            </div>
          </div>
        </div>
      </section>
    </CmsPageShell>
  );
}
