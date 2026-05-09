"use client";

import { useLocale } from "@/context/LocaleContext";
import { cmsImage } from "@/lib/cmsPaths";
import CmsPageShell from "./CmsPageShell";
import CmsSectionHeader from "./CmsSectionHeader";
import CmsBandTitle from "./CmsBandTitle";

function T({ k }) {
  const { t } = useLocale();
  return <>{t(`pages.installations.${k}`)}</>;
}

function Img({ path, className = "" }) {
  return (
    <div className={`cms-img-cell ${className}`}>
      {/* eslint-disable-next-line @next/next/no-img-element */}
      <img src={cmsImage(path)} alt="" className="rounded-1" />
    </div>
  );
}

export default function InstallationsContent() {
  return (
    <CmsPageShell titleKey="pages.meta.installationsTitle">
      <section className="cms-intro pt-4 pb-4">
        <div className="boxcar-container">
          <div className="row">
            <div className="col-12 text-center">
              <h1 className="mb-4">
                <T k="dic_installations_imexso_infra_insta" />
              </h1>
              <p className="mb-3">
                <T k="dic_installations_parc_auto_1" />{" "}
                <strong>
                  <T k="dic_installations_parc_auto_2" />
                </strong>{" "}
                <T k="dic_installations_parc_auto_3" />{" "}
                <strong>
                  <T k="dic_installations_parc_auto_4" />
                </strong>
              </p>
              <p className="mb-3">
                <T k="dic_installations_atelier_meca_1" />{" "}
                <strong>
                  <T k="dic_installations_atelier_meca_2" />
                </strong>{" "}
                <T k="dic_installations_atelier_meca_3" />{" "}
                <strong>
                  <T k="dic_installations_atelier_meca_4" />
                </strong>{" "}
                <T k="dic_installations_atelier_meca_5" />
              </p>
              <p className="mb-0">
                <T k="dic_installations_preparation_1" />{" "}
                <strong>
                  <T k="dic_installations_preparation_2" />
                </strong>
                .
              </p>
            </div>
          </div>
        </div>
      </section>

      <section>
        <CmsSectionHeader>
          <T k="dic_installations_PARC_AUTO" />
        </CmsSectionHeader>
        <div className="cms-section-body">
          <div className="boxcar-container py-2">
            <div className="row g-1 cms-img-grid align-items-stretch">
              <div className="col-md-3 d-flex">
                <Img path="parc2.jpg" className="flex-fill" />
              </div>
              <div className="col-md-6 d-flex">
                <Img path="parc1.jpg" className="flex-fill" />
              </div>
              <div className="col-md-3 d-flex">
                <Img path="parc4.jpg" className="flex-fill" />
              </div>
            </div>
            <div className="row g-1 cms-img-grid mt-1">
              <div className="col-md-6">
                <Img path="parc6.jpg" />
              </div>
              <div className="col-md-6">
                <Img path="parc5.jpg" />
              </div>
            </div>
          </div>
        </div>
      </section>

      <section>
        <CmsSectionHeader>
          <T k="dic_installations_PREPARATION" />
        </CmsSectionHeader>
        <CmsBandTitle>
          <T k="dic_installations_CAR_WASH" />
        </CmsBandTitle>
        <div className="cms-section-body">
          <div className="boxcar-container py-2">
            <div className="row g-1 cms-img-grid">
              <div className="col-md-3">
                <Img path="carwash1.jpg" />
              </div>
              <div className="col-md-3">
                <Img path="carwash2.jpg" />
              </div>
              <div className="col-md-6">
                <Img path="carwash3.jpg" />
              </div>
            </div>
          </div>
        </div>
        <CmsBandTitle>
          <T k="dic_installations_STUDIO_PHOTO" />
        </CmsBandTitle>
        <div className="cms-section-body pb-4">
          <div className="boxcar-container py-2">
            <div className="row g-1 cms-img-grid">
              <div className="col-md-6">
                <Img path="studio1.jpg" />
              </div>
              <div className="col-md-3">
                <Img path="studio2.jpg" />
              </div>
              <div className="col-md-3">
                <Img path="studio3.jpg" />
              </div>
            </div>
          </div>
        </div>
      </section>

      <section>
        <CmsSectionHeader>
          <T k="dic_installations_ATELIER_VEHICULE_REMPLACEMENT" />
        </CmsSectionHeader>
        <CmsBandTitle>
          <T k="dic_installations_MECANIQUE" />
        </CmsBandTitle>
        <div className="cms-section-body">
          <div className="boxcar-container py-2">
            <div className="row g-1 cms-img-grid">
              <div className="col-md-3">
                <Img path="meca1.jpg" />
              </div>
              <div className="col-md-3">
                <Img path="meca2.jpg" />
              </div>
              <div className="col-md-6">
                <Img path="_CD20245.jpg" />
              </div>
            </div>
          </div>
        </div>
        <CmsBandTitle>
          <T k="dic_installations_MONTAGE_PNEUS" />
        </CmsBandTitle>
        <div className="cms-section-body pb-4">
          <div className="boxcar-container py-2">
            <div className="row g-1 cms-img-grid">
              <div className="col-md-3">
                <Img path="pneus1.jpg" />
              </div>
              <div className="col-md-6">
                <Img path="pneus2.jpg" />
              </div>
              <div className="col-md-3">
                <Img path="pneus3.jpg" />
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="pb-5">
        <CmsSectionHeader>
          <T k="dic_installations_SHOWROOM_DEPOTS" />
        </CmsSectionHeader>
        <div className="cms-section-body">
          <div className="boxcar-container py-2">
            <div className="row g-1 cms-img-grid">
              <div className="col-md-3">
                <Img path="show1.jpg" />
              </div>
              <div className="col-md-6">
                <Img path="show2.jpg" />
              </div>
              <div className="col-md-3">
                <Img path="show3.jpg" />
              </div>
            </div>
          </div>
        </div>
      </section>
    </CmsPageShell>
  );
}
