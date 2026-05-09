"use client";

import {
  blogLinks,
  homeLinks,
  megaMenuData,
  pages,
  shopLinks,
} from "@/data/menu";
import Link from "next/link";
import { usePathname } from "next/navigation";
import React from "react";

export default function Nav() {
  const pathname = usePathname();
  const isMenuActive = (menuItem) => {
    let active = false;
    if (menuItem.href?.includes("/")) {
      if (menuItem.href?.split("/")[1] == pathname.split("/")[1]) {
        active = true;
      }
    }
    if (menuItem.length) {
      active = menuItem.some(
        (elm) => elm.href?.split("/")[1] == pathname.split("/")[1]
      );
    }
    if (menuItem.length) {
      menuItem.forEach((item) => {
        item.links?.forEach((elm2) => {
          if (elm2.href?.includes("/")) {
            if (elm2.href?.split("/")[1] == pathname.split("/")[1]) {
              active = true;
            }
          }
          if (elm2.length) {
            elm2.forEach((item2) => {
              item2?.links?.forEach((elm3) => {
                if (elm3.href.split("/")[1] == pathname.split("/")[1]) {
                  active = true;
                }
              });
            });
          }
        });
        if (item.href?.includes("/")) {
          if (item.href?.split("/")[1] == pathname.split("/")[1]) {
            active = true;
          }
        }
      });
    }

    return active;
  };

  return (
    <>
      <li className="current-dropdown current">
        <span className={isMenuActive(homeLinks) ? "menuActive" : ""}>
          Home <i className="fa-solid fa-angle-down" />
        </span>
        <ul className="dropdown">
          {homeLinks.map((link, index) => (
            <li key={index}>
              <Link
                className={isMenuActive(link) ? "menuActive" : ""}
                href={link.href}
              >
                {link.title}
              </Link>
            </li>
          ))}
        </ul>
      </li>
      <li className="current-dropdown">
        <span className={isMenuActive(megaMenuData) ? "menuActive" : ""}>
          Inventory <i className="fa-solid fa-angle-down" />
        </span>
        <div className="mega-menu">
          {megaMenuData.map((column, index) => (
            <div className="mega-column" key={index}>
              <h3 className={isMenuActive(column) ? "menuActive" : ""}>
                {column.title}
              </h3>
              <ul>
                {column.links.map((link, linkIndex) => (
                  <li key={linkIndex}>
                    <Link
                      className={
                        !link.inactive
                          ? isMenuActive(link)
                            ? "menuActive"
                            : ""
                          : ""
                      }
                      href={link.href}
                      title=""
                    >
                      {link.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
      </li>
      <li className="current-dropdown">
        <span className={isMenuActive(blogLinks) ? "menuActive" : ""}>
          Blog <i className="fa-solid fa-angle-down" />
        </span>
        <ul className="dropdown">
          {blogLinks.map((link, index) => (
            <li key={index}>
              <Link
                className={isMenuActive(link) ? "menuActive" : ""}
                href={link.href}
              >
                {link.label}
              </Link>
            </li>
          ))}
        </ul>
      </li>
      <li className="current-dropdown">
        <span className={isMenuActive(shopLinks) ? "menuActive" : ""}>
          Shop <i className="fa-solid fa-angle-down" />
        </span>
        <ul className="dropdown">
          {shopLinks.map((link, index) => (
            <li key={index}>
              <Link
                className={isMenuActive(link) ? "menuActive" : ""}
                href={link.href}
              >
                {link.label}
              </Link>
            </li>
          ))}
        </ul>
      </li>
      <li className="current-dropdown right-one">
        <span className={isMenuActive(pages) ? "menuActive" : ""}>
          Pages <i className="fa-solid fa-angle-down" />
        </span>
        <ul className="dropdown">
          {pages.map((page, index) => (
            <li className={page.links ? "nav-sub" : ""} key={index}>
              {page.href?.includes("/") ? (
                <Link
                  href={page.href}
                  className={isMenuActive(page) ? "menuActive" : ""}
                >
                  {page.title}{" "}
                  {page.iconClass && <i className={page.iconClass} />}
                </Link>
              ) : (
                <a className={isMenuActive(page.links) ? "menuActive" : ""}>
                  {page.title}{" "}
                  {page.iconClass && <i className={page.iconClass} />}
                </a>
              )}
              {page.links && (
                <ul className="dropdown deep subnav-menu">
                  {page.links.map((subItem, subIndex) => (
                    <li key={subIndex}>
                      <Link
                        className={isMenuActive(subItem) ? "menuActive" : ""}
                        href={subItem.href}
                        title=""
                      >
                        {subItem.title}
                      </Link>
                    </li>
                  ))}
                </ul>
              )}
            </li>
          ))}
        </ul>
      </li>
      <li>
        <Link
          className={pathname == "/contact" ? "menuActive" : ""}
          href={`/contact`}
        >
          Contact
        </Link>
      </li>
    </>
  );
}
