"use client";
import { useEffect, useState } from "react";
import Slider from "react-slick";
import Link from "next/link";
import { apiGet } from "@/lib/api";
import CarPhoto from "@/components/common/CarPhoto";
import { getCarImageUrl } from "@/lib/carDisplay";

function formatPrice(price) {
  if (!price && price !== 0) return "N/A";
  return "€" + Math.round(price).toLocaleString();
}

export default function RelatedCars({ make, currentCarId }) {
  const [cars, setCars] = useState([]);

  useEffect(() => {
    if (!make) return;
    async function fetchRelated() {
      try {
        const res = await apiGet(`/api/cars?make=${encodeURIComponent(make)}&per_page=8`);
        if (res.ok) {
          const json = await res.json();
          const filtered = (json.data || []).filter((c) => c.id !== currentCarId);
          setCars(filtered.slice(0, 6));
        }
      } catch {
        // silently fail
      }
    }
    fetchRelated();
  }, [make, currentCarId]);

  if (cars.length === 0) return null;

  return (
    <section className="cars-section-three">
      <div className="boxcar-container">
        <div className="boxcar-title wow fadeInUp">
          <h2>Related Listings</h2>
          <Link href="/inventory" className="btn-title">
            View All
            <svg xmlns="http://www.w3.org/2000/svg" width={14} height={14} viewBox="0 0 14 14" fill="none">
              <g clipPath="url(#clip_va)"><path d="M13.6109 0H5.05533C4.84037 0 4.66643 0.173943 4.66643 0.388901C4.66643 0.603859 4.84037 0.777802 5.05533 0.777802H12.6721L0.113697 13.3362C-0.0382246 13.4881 -0.0382246 13.7342 0.113697 13.8861C0.18964 13.962 0.289171 14 0.388666 14C0.488161 14 0.587656 13.962 0.663635 13.8861L13.222 1.3277V8.94447C13.222 9.15943 13.3959 9.33337 13.6109 9.33337C13.8259 9.33337 13.9998 9.15943 13.9998 8.94447V0.388901C13.9998 0.173943 13.8258 0 13.6109 0Z" fill="#050B20" /></g>
              <defs><clipPath id="clip_va"><rect width={14} height={14} fill="white" /></clipPath></defs>
            </svg>
          </Link>
        </div>
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
          ]}
          arrows
          className="car-slider-three car-slider-three--related wow fadeInUp"
        >
          {cars.map((car) => {
            const imageUrl = getCarImageUrl(car);
            const title = [car.make, car.model].filter(Boolean).join(" ") || "Unknown Vehicle";
            return (
              <div key={car.id} className="car-block-three col-12">
                <div className="inner-box">
                  <div className="image-box">
                    <div className="slider-thumb">
                      <div className="image">
                        <Link href={`/car/${car.id_produit}`}>
                          <CarPhoto src={imageUrl} alt={title} width={329} height={220} variant="slider" className="related-car-card-img" />
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
                        View Details
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
