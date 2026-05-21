export default function HomeStatCardIcon({ type }) {
  switch (type) {
    case "team":
      return (
        <svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
          <circle cx="24" cy="14" r="6" fill="currentColor" />
          <path
            d="M8 38c0-6.627 5.373-10 10-10h12c4.627 0 10 3.373 10 10"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
          />
          <circle cx="14" cy="18" r="4" fill="currentColor" opacity="0.85" />
          <circle cx="34" cy="18" r="4" fill="currentColor" opacity="0.85" />
          <path
            d="M4 38c0-4 3-7 7-7M44 38c0-4-3-7-7-7"
            stroke="currentColor"
            strokeWidth="2.5"
            strokeLinecap="round"
            opacity="0.85"
          />
        </svg>
      );
    case "stock":
      return (
        <svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
          <path
            d="M8 22h32l-2 14H10L8 22z"
            fill="currentColor"
          />
          <path
            d="M10 22l3-8h22l3 8"
            stroke="currentColor"
            strokeWidth="2.5"
            strokeLinejoin="round"
          />
          <circle cx="14" cy="36" r="3" fill="#fff" stroke="currentColor" strokeWidth="2" />
          <circle cx="34" cy="36" r="3" fill="#fff" stroke="currentColor" strokeWidth="2" />
          <rect x="16" y="10" width="16" height="8" rx="2" fill="currentColor" />
        </svg>
      );
    case "history":
      return (
        <svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
          <text
            x="24"
            y="22"
            textAnchor="middle"
            fill="currentColor"
            fontSize="7"
            fontWeight="700"
            fontFamily="Arial, sans-serif"
          >
            since
          </text>
          <text
            x="24"
            y="34"
            textAnchor="middle"
            fill="currentColor"
            fontSize="11"
            fontWeight="800"
            fontFamily="Arial, sans-serif"
          >
            1989
          </text>
        </svg>
      );
    case "installations":
      return (
        <svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
          <path
            d="M6 24h14v18H6V24zM28 16h14v26H28V16z"
            fill="currentColor"
          />
          <path
            d="M22 28h8v14h-8V28z"
            fill="currentColor"
            opacity="0.9"
          />
          <path
            d="M12 30h6v4h-6v-4zM32 22h6v4h-6v-4z"
            fill="#fff"
            opacity="0.35"
          />
          <path
            d="M10 36h10l2-6h6l2 6"
            stroke="#fff"
            strokeWidth="1.5"
            strokeLinejoin="round"
          />
        </svg>
      );
    default:
      return null;
  }
}
