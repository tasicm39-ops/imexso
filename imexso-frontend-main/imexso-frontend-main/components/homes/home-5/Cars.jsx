"use client";
import Image from "next/image";
import Slider from "react-slick";
import Link from "next/link";
import { useEffect, useState } from "react";
import { apiGet } from "@/lib/api";
import { useAuth } from "@/context/AuthContext";
import { useLocale } from "@/context/LocaleContext";

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

export default function Cars() {
  const { isAuthenticated, isValidated } = useAuth();
  const { t } = useLocale();
  const [cars, setCars] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!isAuthenticated || !isValidated) {
      setLoading(false);
      return;
    }
    async function fetchCars() {
      try {
        const res = await apiGet("/api/cars?per_page=8&sort=newest");
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

  if (!isAuthenticated || !isValidated) {
    return (
      <section className="cars-section-eight pt-0">
        <div className="boxcar-container">
          <div className="boxcar-title text-center wow fadeInUp">
            <h2>{t("home.most_searched_cars")}</h2>
          </div>
          <div className="d-flex flex-column justify-content-center align-items-center" style={{ minHeight: "200px" }}>
            <p>{t("home.log_in_to_browse")}</p>
            <Link
              href="/login"
              className="mt-3"
              style={{
                display: "inline-flex",
                alignItems: "center",
                gap: "8px",
                padding: "12px 36px",
                background: "#405FF2",
                color: "#fff",
                fontSize: "15px",
                fontWeight: 600,
                borderRadius: "12px",
                textDecoration: "none",
                transition: "background 0.2s",
              }}
              onMouseEnter={(e) => (e.currentTarget.style.background = "#0146a6")}
              onMouseLeave={(e) => (e.currentTarget.style.background = "#405FF2")}
            >
              {t("general.log_in")}
              <Image alt="" src="/images/arrow.svg" width={14} height={14} />
            </Link>
          </div>
        </div>
      </section>
    );
  }

  if (loading) {
    return (
      <section className="cars-section-eight pt-0">
        <div className="boxcar-container">
          <div className="boxcar-title text-center wow fadeInUp">
            <h2>{t("home.most_searched_cars")}</h2>
          </div>
          <div className="d-flex justify-content-center" style={{ minHeight: "200px" }}>
            <div className="spinner-border" role="status">
              <span className="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </section>
    );
  }

  if (cars.length === 0) return null;

  return (
    <section className="cars-section-eight pt-0">
      <div className="boxcar-container">
        <div className="boxcar-title text-center wow fadeInUp">
          <h2>{t("home.most_searched_cars")}</h2>
        </div>
        <div className="tab-content wow fadeInUp" id="nav-tabContent">
          <div className="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
            <Slider
              slidesToScroll={1}
              slidesToShow={4}
              infinite={cars.length > 4}
              responsive={[
                { breakpoint: 1600, settings: { slidesToShow: 4, slidesToScroll: 1, arrows: true, infinite: cars.length > 4 } },
                { breakpoint: 1300, settings: { slidesToShow: 3, slidesToScroll: 1, infinite: cars.length > 3 } },
                { breakpoint: 991, settings: { slidesToShow: 2, slidesToScroll: 1, infinite: cars.length > 2 } },
                { breakpoint: 767, settings: { slidesToShow: 1, slidesToScroll: 1 } },
                { breakpoint: 576, settings: { slidesToShow: 1, slidesToScroll: 1 } },
                { breakpoint: 480, settings: { slidesToShow: 1, slidesToScroll: 1 } },
              ]}
              arrows
              className="car-slider-three car-slider-three--home"
            >
              {cars.map((car) => {
                const imageUrl = car.photos?.[0]?.url || null;
                const title = [car.make, car.model].filter(Boolean).join(" ") || "Unknown Vehicle";
                return (
                  <div key={car.id} className="box-car car-block-five col-12 col-md-6 col-lg-3">
                    <div className="inner-box">
                      <div className="image-box">
                        <div className="slider-thumb">
                          <div className="image">
                            <Link href={`/car/${car.id_produit}`}>
                              <CarImage src={imageUrl} alt={title} width={329} height={220} />
                            </Link>
                          </div>
                        </div>
                        {car.is_new && <span>New</span>}
                        {car.is_clearance && <span>Clearance</span>}
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
        </div>
      </div>
    </section>
  );
}
