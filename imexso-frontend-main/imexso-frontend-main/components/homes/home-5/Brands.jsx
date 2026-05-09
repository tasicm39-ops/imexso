"use client";
import React, {useEffect, useState} from "react";
import Link from "next/link";
import {apiGet} from "@/lib/api";
import {useAuth} from "@/context/AuthContext";

const CATEGORY_ICONS = {
  VP: "flaticon-car-1",
  VU: "flaticon-van",
  SUV: "flaticon-car",
  SEDAN: "flaticon-car-1",
  HATCHBACK: "flaticon-van",
  COUPE: "flaticon-convertible-car",
  HYBRID: "flaticon-electric-car-1",
  ELECTRIC: "flaticon-electric-car-2",
  TRUCK: "flaticon-pick-up-truck",
};

function getIconForCategory(category) {
  const upper = (category || "").toUpperCase();
  return CATEGORY_ICONS[upper] || "flaticon-car";
}

export default function Brands() {
  const { isAuthenticated, isValidated } = useAuth();
  const [categories, setCategories] = useState([]);

  useEffect(() => {
    if (!isAuthenticated || !isValidated) return;

    async function fetchCategories() {
      try {
        const res = await apiGet("/api/cars/filters");
        if (res.ok) {
          const data = await res.json();
          setCategories(data.categories || []);
        }
      } catch {
        // silently fail
      }
    }

    fetchCategories();
  }, [isAuthenticated, isValidated]);

  if (categories.length === 0) return null;

  return (
      <section className="boxcar-brand-section-four">
        <div className="boxcar-container">
          <div className="boxcar-title text-center">
            <h2>Browse by Type</h2>
          </div>
          <div className="right-box">
            {categories.map((category, index) => (
                <div key={index} className="brand-block-four">
                  <div className="inner-box">
                    <div className="icon-box">
                      <i className={getIconForCategory(category)}/>
                </div>
                <h6 className="title">
                  <Link href={`/inventory?category=${encodeURIComponent(category)}`}>
                    {category}
                  </Link>
                </h6>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
