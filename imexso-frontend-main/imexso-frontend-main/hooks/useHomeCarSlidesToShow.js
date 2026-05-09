import { useEffect, useState } from "react";

/**
 * react-slick applies top-level slidesToShow until responsive media queries
 * run after mount; default slidesToShow={4} briefly shows four narrow cards on
 * mobile. We derive the count from viewport immediately on the client.
 */
export function useHomeCarSlidesToShow() {
  const [slidesToShow, setSlidesToShow] = useState(1);

  useEffect(() => {
    function update() {
      const w = window.innerWidth;
      if (w < 768) setSlidesToShow(1);
      else if (w < 992) setSlidesToShow(2);
      else if (w < 1300) setSlidesToShow(3);
      else setSlidesToShow(4);
    }
    update();
    window.addEventListener("resize", update);
    return () => window.removeEventListener("resize", update);
  }, []);

  return slidesToShow;
}
