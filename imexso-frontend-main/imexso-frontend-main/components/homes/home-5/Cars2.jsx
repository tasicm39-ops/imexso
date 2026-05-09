"use client";
import Image from "next/image";
import Slider from "react-slick";
import Link from "next/link";
import { useEffect, useState } from "react";
import { apiGet } from "@/lib/api";
import { useAuth } from "@/context/AuthContext";
import { useLocale } from "@/context/LocaleContext";
import { useHomeCarSlidesToShow } from "@/hooks/useHomeCarSlidesToShow";

function formatPrice(price) {
  if (!price && price !== 0) return "N/A";
  return "€" + Math.round(price).toLocaleString();
}

function CarImage({ src, alt, width, height }) {
  const [error, setError] = useState(false);
  if (error || !src) {
    return (
      <div
        style={{
          width: "100%",
          height: "220px",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          background: "#e9ecef",
          color: "#999",
          fontSize: "14px",
        }}
      >
        No Preview
      </div>
    );
  }
  return (
    <Image
      alt={alt}
      src={src}
      width={width}
      height={height}
      unoptimized
      onError={() => setError(true)}
    />
  );
}

export default function Cars2() {
  const { isAuthenticated, isValidated } = useAuth();
  const { t } = useLocale();
  const slidesToShow = useHomeCarSlidesToShow();
  const [cars, setCars] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!isAuthenticated || !isValidated) {
      setLoading(false);
      return;
    }
    async function fetchCars() {
      try {
        const res = await apiGet("/api/cars?per_page=8&sort=price_asc");
        if (res.ok) {
          const json = await res.json();
          setCars(json.data || []);
        }
      } catch {
        // silently fail
      } finally {
        setLoading(false);
      }
    }
    fetchCars();
  }, [isAuthenticated, isValidated]);

  if (!isAuthenticated || !isValidated || loading || cars.length === 0) {
    return null;
  }

  return (
    <section className="cars-section-seven pt-0">
      <div className="boxcar-container">
        <div className="boxcar-title text-center wow fadeInUp">
          <h2>{t("home.latest_cars")}</h2>
        </div>
        <Slider
          slidesToScroll={1}
          slidesToShow={slidesToShow}
          infinite={cars.length > slidesToShow}
          arrows
          className="row car-slider-three"
        >
          {cars.map((car) => {
            const imageUrl = car.photos?.[0]?.url || null;
            const title = [car.make, car.model].filter(Boolean).join(" ") || "Unknown Vehicle";
            return (
              <div key={car.id} className="box-car car-block-five col-lg-3 col-md-6 col-sm-12">
                <div className="inner-box">
                  <div className="image-box">
                    <div className="slider-thumb">
                      <div className="image">
                        <Link href={`/car/${car.id_produit}`}>
                          <CarImage src={imageUrl} alt={title} width={329} height={220} />
                        </Link>
                      </div>
                    </div>
                  </div>
                  <div className="content-box">
                    <h6 className="title">
                      <Link href={`/car/${car.id_produit}`}>{title}</Link>
                    </h6>
                    <div className="text">{car.trim_level || ""}</div>
                    <ul>
                      <li><i className="flaticon-gasoline-pump" /> {car.mileage ? Number(car.mileage).toLocaleString() + " km" : "N/A"}</li>
                      <li><i className="flaticon-speedometer" /> {car.fuel_type || "N/A"}</li>
                      <li><i className="flaticon-gearbox" /> {car.gearbox || "N/A"}</li>
                    </ul>
                    <div className="btn-box">
                      <span>{formatPrice(car.professional_price)}</span>
                      <Link href={`/car/${car.id_produit}`} className="details">
                        {t("general.view_details")}
                        <svg xmlns="http://www.w3.org/2000/svg" width={14} height={14} viewBox="0 0 14 14" fill="none">
                          <g clipPath="url(#clip0_634_448)"><path d="M13.6109 0H5.05533C4.84037 0 4.66643 0.173943 4.66643 0.388901C4.66643 0.603859 4.84037 0.777802 5.05533 0.777802H12.6721L0.113697 13.3362C-0.0382246 13.4881 -0.0382246 13.7342 0.113697 13.8861C0.18964 13.962 0.289171 14 0.388666 14C0.488161 14 0.587656 13.962 0.663635 13.8861L13.222 1.3277V8.94447C13.222 9.15943 13.3959 9.33337 13.6109 9.33337C13.8259 9.33337 13.9998 9.15943 13.9998 8.94447V0.388901C13.9998 0.173943 13.8258 0 13.6109 0Z" fill="white" /></g>
                          <defs><clipPath id="clip0_634_448"><rect width={14} height={14} fill="white" /></clipPath></defs>
                        </svg>
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            );
          })}
        </Slider>
      </div>
    </section>
  );
}
