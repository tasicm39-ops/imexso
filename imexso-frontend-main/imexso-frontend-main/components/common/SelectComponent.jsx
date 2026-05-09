"use client";

import { useEffect, useRef, useState } from "react";

export default function SelectComponent({
  options = [],
  value,
  onChange,
  placeholder,
}) {
  const isControlled = onChange !== undefined;
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [internalSelected, setInternalSelected] = useState(options[0] || "");
  const ref = useRef(null);

  const displayValue = isControlled
    ? value || placeholder || "Select..."
    : internalSelected;

  useEffect(() => {
    function handleClickOutside(event) {
      if (ref.current && !ref.current.contains(event.target)) {
        setIsDropdownOpen(false);
      }
    }
    document.addEventListener("click", handleClickOutside);
    return () => document.removeEventListener("click", handleClickOutside);
  }, []);

  const handleSelect = (option) => {
    if (isControlled) {
      onChange(option === placeholder ? "" : option);
    } else {
      setInternalSelected(option);
    }
    setIsDropdownOpen(false);
  };

  const handleOptionPointer = (event, option) => {
    event.preventDefault();
    event.stopPropagation();
    handleSelect(option);
  };

  const displayOptions =
    isControlled && placeholder ? [placeholder, ...options] : options;

  return (
    <div ref={ref} className={`drop-menu ${isDropdownOpen ? "active" : ""}`}>
      <div
        className="select"
        onClick={(event) => {
          event.stopPropagation();
          setIsDropdownOpen((prev) => !prev);
        }}
      >
        <span>{displayValue}</span>
        <i className="fa fa-angle-down" />
      </div>
      <ul
        className="dropdown"
        style={
          isDropdownOpen
            ? {
                display: "block",
                opacity: 1,
                visibility: "visible",
                transition: "0.4s",
              }
            : {
                display: "block",
                opacity: 0,
                visibility: "hidden",
                transition: "0.4s",
              }
        }
      >
        {displayOptions.map((option, index) => (
          <li
            key={index}
            onPointerDown={(event) => handleOptionPointer(event, option)}
          >
            {option}
          </li>
        ))}
      </ul>
    </div>
  );
}
