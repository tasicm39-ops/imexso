"use client";
import React, {useEffect, useRef, useState} from "react";
import Image from "next/image";
import SelectComponent from "@/components/common/SelectComponent";
import Link from "next/link";
import {useRouter} from "next/navigation";
import {apiGet} from "@/lib/api";
import {useAuth} from "@/context/AuthContext";
import {useLocale} from "@/context/LocaleContext";

export default function Hero() {
  const {isAuthenticated, isValidated} = useAuth();
  const {t} = useLocale();
  const router = useRouter();
  const [filterOptions, setFilterOptions] = useState(null);
  const [selectedCondition, setSelectedCondition] = useState("");
  const [selectedMake, setSelectedMake] = useState("");
  const [selectedModel, setSelectedModel] = useState("");
  const selectedConditionRef = useRef("");
  const selectedMakeRef = useRef("");
  const selectedModelRef = useRef("");

  useEffect(() => {
    if (!isAuthenticated || !isValidated) return;

    async function fetchFilters() {
      try {
        const res = await apiGet("/api/cars/filters");
        if (res.ok) {
          const data = await res.json();
          setFilterOptions(data);
        }
      } catch {
        // silently fail
      }
    }
    fetchFilters();
  }, [isAuthenticated, isValidated]);

  const conditionOptions = filterOptions?.condition_types || ["Used Cars"];
  const makeOptions = filterOptions?.makes || ["Any Makes"];
  const modelOptions =
    selectedMake && filterOptions?.models_by_make
      ? filterOptions.models_by_make[selectedMake] || ["Any Models"]
      : ["Any Models"];

  const buildSearchUrl = () => {
    const condition =
      selectedConditionRef.current || selectedCondition;
    const make = selectedMakeRef.current || selectedMake;
    const model = selectedModelRef.current || selectedModel;
    const params = new URLSearchParams();
    if (condition) params.set("condition_type", condition);
    if (make) params.set("make", make);
    if (model) params.set("model", model);
    const qs = params.toString();
    return `/inventory${qs ? "?" + qs : ""}`;
  };

  const isMobileSearchViewport = () =>
    typeof window !== "undefined" &&
    window.matchMedia("(max-width: 767.98px)").matches;

  const handleSearchSubmit = (e) => {
    e.preventDefault();
    if (isMobileSearchViewport()) {
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          router.push(buildSearchUrl());
        });
      });
      return;
    }
    router.push(buildSearchUrl());
  };

  return (
      <div>
        <div
            style={{height: 200, width: '100%', marginTop: -2, backgroundColor: '#050b20', position: 'absolute'}}>
        </div>
        <section className="boxcar-banner-section-five section-radius-top" style={{marginTop: 0}}>
          <div className="banner-content-three">
            <div className="boxcar-container">
              <div className="banner-content">
            <span className="wow fadeInUp">
              {t("home.hero_subtitle")}
            </span>
                <h2 className="wow fadeInUp" data-wow-delay="100ms">
                  {t("home.hero_title")}
                </h2>
                <div className="form-tabs wow fadeInUp" data-wow-delay="200ms">
                  <div className="form-tab-pane current" id="tab-1">
                    <form onSubmit={handleSearchSubmit}>
                      <div className="form_boxes">
                        <SelectComponent
                            options={conditionOptions}
                            value={selectedCondition}
                            onChange={(val) => {
                              selectedConditionRef.current = val;
                              setSelectedCondition(val);
                            }}
                      placeholder={t("home.all_conditions")}
                    />
                  </div>
                  <div className="form_boxes">
                    <SelectComponent
                      options={makeOptions}
                      value={selectedMake}
                      onChange={(val) => {
                        selectedMakeRef.current = val;
                        selectedModelRef.current = "";
                        setSelectedMake(val);
                        setSelectedModel("");
                      }}
                      placeholder={t("home.any_makes")}
                    />
                  </div>
                  <div className="form_boxes">
                    <SelectComponent
                      options={modelOptions}
                      value={selectedModel}
                      onChange={(val) => {
                        selectedModelRef.current = val;
                        setSelectedModel(val);
                      }}
                      placeholder={t("home.any_models")}
                    />
                  </div>
                  <div className="form-submit">
                    <button type="submit" className="theme-btn">
                      <i className="flaticon-search" />
                    </button>
                  </div>
                </form>
              </div>
            </div>
            <div className="image-column">
              <div className="image-box">
                <figure className="image">
                  <Link href="/inventory">
                    <Image
                        alt=""
                        src="/images/banner/transparent-1216x738.png"
                        width={500}
                        height={500}
                    />
                  </Link>
                </figure>
              </div>
            </div>
              </div>
            </div>
          </div>
        </section>
      </div>
  );
}
