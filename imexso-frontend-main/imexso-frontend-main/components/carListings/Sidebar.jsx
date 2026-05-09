"use client";
import React from "react";
import Image from "next/image";
import FilterForm from "./FilterForm";

export default function Sidebar({
  filterOptions,
  activeFilters,
  onFilterChange,
  onResetFilters,
}) {
  return (
    <div className="wrap-fixed-sidebar">
      <div className="sidebar-backdrop" />
      <div className="widget-sidebar-filter">
        <div className="fixed-sidebar-title">
          <h3>More Filter</h3>
          <a href="#" title="" className="close-filters">
            <Image
              alt=""
              src="/images/icons/close.svg"
              width={30}
              height={30}
            />
          </a>
        </div>
        <div className="inventory-sidebar">
          <FilterForm
            filterOptions={filterOptions}
            activeFilters={activeFilters}
            onFilterChange={onFilterChange}
            onResetFilters={onResetFilters}
          />
        </div>
      </div>
    </div>
  );
}
