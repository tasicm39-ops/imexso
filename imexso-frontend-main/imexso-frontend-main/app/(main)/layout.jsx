import ProtectedRoute from "@/components/auth/ProtectedRoute";

export default function MainLayout({ children }) {
    return (
        <ProtectedRoute requireValidation={true}>
            {children}
        </ProtectedRoute>
    );
}
