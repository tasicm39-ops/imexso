"use client";

import { useLocale } from "@/context/LocaleContext";
import { cmsImage } from "@/lib/cmsPaths";
import CmsBandTitle from "./CmsBandTitle";
import CmsPageShell from "./CmsPageShell";

function T({ k }) {
  const { t } = useLocale();
  return <>{t(`pages.engagements.${k}`)}</>;
}

export default function EngagementsContent() {
  return (
    <CmsPageShell titleKey="pages.meta.engagementsTitle">
      <section className="py-4">
        <div className="boxcar-container text-center">
          <h1 className="fw-bold mb-3">
            <T k="dic_engagements_VEHICULES_NEUFS_OCCASION" />
          </h1>
          <p className="fw-bold fs-5 mb-0">
            <T k="dic_engagements_Tapis_rouge" />
          </p>
        </div>
      </section>

      {/* SERVICES DE QUALITE */}
      <section className="pb-3">
        <div className="container-fluid px-0">
          <h2 className="cms-engagement-part text-center mb-0">
            <T k="dic_engagements_SERICE_QUALITE" />
          </h2>
        </div>

        <CmsBandTitle>
          <T k="dic_engagements_GARANTIES" />
        </CmsBandTitle>
        <div className="cms-section-body py-3">
          <div className="boxcar-container">
            <div className="row g-0">
              <div className="col-md-3">
                <div className="cms-bg-tint h-100">
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img
                    src={cmsImage("documentsapp.jpg")}
                    alt=""
                    className="w-100 h-100"
                    style={{ objectFit: "cover" }}
                  />
                </div>
              </div>
              <div className="col-md-9">
                <div className="h-100 pt-4 px-4 pb-3 cms-bg-tint">
                  <p className="fw-bold">
                    <T k="dic_engagements_Vehicule_garantis" />
                  </p>
                  <ul className="ps-3 mb-2">
                    <li className="mb-2">
                      <T k="dic_engagements_controle_technique_1" />{" "}
                      <strong>
                        <T k="dic_engagements_controle_technique_2" />
                      </strong>
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_garantie_usine_1" />
                      <strong>
                        <T k="dic_engagements_garantie_usine_2" />
                      </strong>
                      .
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_entretien_1" />{" "}
                      <strong>
                        <T k="dic_engagements_entretien_2" />
                      </strong>
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_expertise_30_ans" />
                    </li>
                    <li className="mb-2">
                      <strong>
                        <T k="dic_engagements_extension_garantie_1" />
                      </strong>{" "}
                      <T k="dic_engagements_extension_garantie_2" />
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_traxio_1" />{" "}
                      <strong>
                        <T k="dic_engagements_traxio_2" />
                      </strong>
                      .
                    </li>
                  </ul>
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img
                    src={cmsImage("traxio.jpg")}
                    alt=""
                    width={100}
                    height={40}
                    style={{ width: 100, height: "auto" }}
                  />
                </div>
              </div>
            </div>
            <p className="small fw-bold mt-2 mb-1 cms-text-muted">
              <T k="dic_engagements_conditions_offre" />
            </p>
            <p className="small mb-0 cms-text-muted">
              <T k="dic_engagements_offre_soumise" />
            </p>
          </div>
        </div>

        <CmsBandTitle>
          <T k="dic_engagements_QUALITY_CHECK" />
        </CmsBandTitle>
        <div className="cms-section-body py-3">
          <div className="boxcar-container">
            <div className="row g-0">
              <div className="col-md-3">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("Imexso_2556.jpg")}
                  alt=""
                  className="w-100"
                />
              </div>
              <div className="col-md-9">
                <div className="h-100 pt-4 px-4 pb-3 cms-bg-tint">
                  <p className="fw-bold">
                    <T k="dic_engagements_voiture_mieux" />
                  </p>
                  <ul className="ps-3">
                    <li className="mb-2">
                      <strong>
                        <T k="dic_engagements_vehicule_controles" />
                      </strong>
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_certifiee_qualite_imexso_1" />
                      <em className="fw-bold">
                        <T k="dic_engagements_certifiee_qualite_imexso_2" />
                      </em>
                      .
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_inspection_complete_1" />{" "}
                      <strong>
                        <T k="dic_engagements_inspection_complete_2" />
                      </strong>
                      .
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_attestation_carpass_1" />{" "}
                      <strong>
                        <T k="dic_engagements_attestation_carpass_2" />
                      </strong>
                      .
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_integralite_vehicule_1" />{" "}
                      <strong>
                        <T k="dic_engagements_integralite_vehicule_2" />
                      </strong>
                      .
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <CmsBandTitle>
          <T k="dic_engagements_REMISE_NEUF" />
        </CmsBandTitle>
        <div className="cms-section-body py-3 pb-5">
          <div className="boxcar-container">
            <div className="row g-0">
              <div className="col-md-3">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img src={cmsImage("pneus3.jpg")} alt="" className="w-100" />
              </div>
              <div className="col-md-9">
                <div className="h-100 pt-4 px-4 pb-3 cms-bg-tint">
                  <p className="fw-bold">
                    <T k="dic_engagements_Imexso_comme_neuf" />
                  </p>
                  <ul className="ps-3">
                    <li className="mb-2">
                      <T k="dic_engagements_reconditionnee_1" />{" "}
                      <strong>
                        <T k="dic_engagements_reconditionnee_2" />
                      </strong>{" "}
                      <T k="dic_engagements_reconditionnee_3" />
                    </li>
                    <li className="mb-2">
                      <strong>
                        <T k="dic_engagements_interventions_mecanique_1" />
                      </strong>{" "}
                      <T k="dic_engagements_interventions_mecanique_2" />{" "}
                      <strong>
                        <T k="dic_engagements_interventions_mecanique_3" />
                      </strong>
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_dans_nos_ateliers_1" />{" "}
                      <strong>
                        <T k="dic_engagements_dans_nos_ateliers_2" />
                      </strong>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* AVANTAGES FINANCIERS */}
      <section className="pb-3">
        <div className="container-fluid px-0">
          <h2 className="cms-engagement-part text-center mb-4">
            <T k="dic_engagements_AVANTAGES_FINANCIERS" />
          </h2>
        </div>

        <CmsBandTitle>
          <T k="dic_engagements_REPRISE_VÉHICULE" />
        </CmsBandTitle>
        <div className="cms-section-body py-3">
          <div className="boxcar-container">
            <div className="row g-0">
              <div className="col-md-3">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("05042019-_CD20397.jpg")}
                  alt=""
                  className="w-100"
                />
              </div>
              <div className="col-md-9">
                <div className="h-100 pt-4 px-4 pb-3 cms-bg-tint">
                  <p className="fw-bold">
                    <T k="dic_engagements_Imexso_reprend_vehicule" />
                  </p>
                  <ul className="ps-3">
                    <li className="mb-2">
                      <T k="dic_engagements_Devis_gratuit_1" />{" "}
                      <strong>
                        <T k="dic_engagements_Devis_gratuit_2" />
                      </strong>
                    </li>
                    <li className="mb-2">
                      <strong>
                        <T k="dic_engagements_Paiement_cash_1" />
                      </strong>{" "}
                      <T k="dic_engagements_Paiement_cash_2" />
                    </li>
                    <li className="mb-2">
                      <strong>
                        <T k="dic_engagements_controle_technique_1" />
                      </strong>{" "}
                      <T k="dic_engagements_controle_technique_2" />
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_Reprise_etat_1" />{" "}
                      <strong>
                        <T k="dic_engagements_Reprise_etat_2" />
                      </strong>
                    </li>
                    <li className="mb-2">
                      <strong>
                        <T k="dic_engagements_Meilleur_prix_marche_1" />
                      </strong>{" "}
                      <T k="dic_engagements_Meilleur_prix_marche_2" />
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_Prestation_sans_frais_1" />{" "}
                      <strong>
                        <T k="dic_engagements_Prestation_sans_frais_2" />
                      </strong>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <CmsBandTitle>
          <T k="dic_engagements_REMISE_IMPORTANTES" />
        </CmsBandTitle>
        <div className="cms-section-body py-3">
          <div className="boxcar-container">
            <div className="row g-0">
              <div className="col-md-3">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img src={cmsImage("parc4.jpg")} alt="" className="w-100" />
              </div>
              <div className="col-md-9">
                <div className="h-100 pt-4 px-4 pb-3 cms-bg-tint">
                  <p className="fw-bold">
                    <T k="dic_engagements_30_ans_experience" />
                  </p>
                  <ul className="ps-3">
                    <li className="mb-2">
                      <T k="dic_engagements_Ventes_publiques" />
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_Retour_location" />
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_Stock_invendus" />
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_Vehicule_direction" />
                    </li>
                  </ul>
                  <p className="fw-bold">
                    <T k="dic_engagements_Garanties_meilleur_prix" />
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <CmsBandTitle>
          <T k="dic_engagements_FORMULES_FINANCEMENT" />
        </CmsBandTitle>
        <div className="cms-section-body py-3 pb-5">
          <div className="boxcar-container">
            <div className="row g-0">
              <div className="col-md-3">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={cmsImage("finscreen.jpg")}
                  alt=""
                  className="w-100"
                />
              </div>
              <div className="col-md-9">
                <div className="h-100 pt-4 px-4 pb-3 cms-bg-tint">
                  <p className="fw-bold">
                    <T k="dic_engagements_Souhaite_financer" />
                  </p>
                  <ul className="ps-3">
                    <li className="mb-2">
                      <T k="dic_engagements_Avec_sans_accompte_1" />{" "}
                      <strong>
                        <T k="dic_engagements_Avec_sans_accompte_2" />
                      </strong>
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_Avec_sans_valeur_1" />{" "}
                      <strong>
                        <T k="dic_engagements_Avec_sans_valeur_2" />
                      </strong>
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_Plusieurs_formules_1" />{" "}
                      <strong>
                        <T k="dic_engagements_Plusieurs_formules_2" />
                      </strong>
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_Simulez_financement_1" />{" "}
                      <strong>
                        <T k="dic_engagements_Simulez_financement_2" />
                      </strong>{" "}
                      <T k="dic_engagements_Simulez_financement_3" />
                    </li>
                    <li className="mb-2">
                      <T k="dic_engagements_FSMA" />
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* TEMOIGNAGES */}
      <section className="pb-5">
        <div className="boxcar-container text-center mb-3">
          <h2 className="fw-bold mb-2">
            <T k="dic_engagements_FAITES_CHOIX_IMEXSO" />
          </h2>
          <h3 className="h4 fw-bold">
            <T k="dic_engagements_AUTO_RISQUE_ZERO" />
          </h3>
        </div>
        <CmsBandTitle>
          <T k="dic_engagements_CLIENTS_SATISFAITS" />
        </CmsBandTitle>
        <div className="cms-section-body py-3">
          <div className="boxcar-container">
            <div className="row g-0">
              <div className="col-md-3">
                <div className="cms-bg-tint h-100">
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img
                    src={cmsImage("cust.jpg")}
                    alt=""
                    className="w-100"
                    style={{ objectFit: "cover" }}
                  />
                </div>
              </div>
              <div className="col-md-9">
                <div className="h-100 pt-4 px-4 pb-3 cms-bg-tint">
                  {[
                    ["dic_engagements_temoignage_1", "dic_engagements_temoignage_2"],
                    ["dic_engagements_temoignage_3", "dic_engagements_temoignage_4"],
                    ["dic_engagements_temoignage_5", "dic_engagements_temoignage_6"],
                    ["dic_engagements_temoignage_7", "dic_engagements_temoignage_8"],
                  ].map(([a, b]) => (
                    <p key={a} className="mb-4">
                      <span className="fst-italic">
                        <T k={a} />
                      </span>
                      <br />
                      <strong className="small">
                        <T k={b} />
                      </strong>
                    </p>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </CmsPageShell>
  );
}
