"use client";

import { useAuth } from "@/context/AuthContext";
import Hero from "@/components/homes/home-5/Hero";
import Brands from "@/components/homes/home-5/Brands";
import Cta from "@/components/common/Cta";
import Cars from "@/components/homes/home-5/Cars";
import HomeStatsCards from "@/components/homes/home-5/HomeStatsCards";
import Features from "@/components/homes/home-5/Features";
import Cars2 from "@/components/homes/home-5/Cars2";
import HomeProAccount from "@/components/homes/home-5/HomeProAccount";

export default function HomePageContent() {
  const { isAuthenticated, isValidated } = useAuth();
  const canBrowse = isAuthenticated && isValidated;

  return (
    <>
      <Hero />
      {canBrowse && (
        <>
          <Brands />
          <Cta />
          <Cars />
        </>
      )}
      <HomeStatsCards />
      <Features redirectGuestsToLogin={!canBrowse} />
      {canBrowse ? <Cars2 /> : <HomeProAccount />}
    </>
  );
}
