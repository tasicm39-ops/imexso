"use client";

import { useState, useRef } from "react";
import Header1 from "@/components/headers/Header1";
import Footer1 from "@/components/footers/Footer1";
import { useAuth } from "@/context/AuthContext";
import { apiPost, apiPostFormData, getCsrfCookie } from "@/lib/api";

export default function ProfilePage() {
    const { user, fetchUser } = useAuth();

    const [name, setName] = useState(user?.name || "");
    const [email] = useState(user?.email || "");
    const [companyName, setCompanyName] = useState(user?.company_name || "");
    const [slogan, setSlogan] = useState(user?.slogan || "");
    const [logoFile, setLogoFile] = useState(null);
    const [logoPreview, setLogoPreview] = useState(
        user?.logo ? `/api/storage/${user.logo}` : null
    );
    const logoInputRef = useRef(null);

    const [currentPassword, setCurrentPassword] = useState("");
    const [newPassword, setNewPassword] = useState("");
    const [confirmPassword, setConfirmPassword] = useState("");
    const [profileMsg, setProfileMsg] = useState("");
    const [passwordMsg, setPasswordMsg] = useState("");
    const [companyMsg, setCompanyMsg] = useState("");
    const [profileErrors, setProfileErrors] = useState({});
    const [passwordErrors, setPasswordErrors] = useState({});
    const [companyErrors, setCompanyErrors] = useState({});
    const [savingProfile, setSavingProfile] = useState(false);
    const [savingPassword, setSavingPassword] = useState(false);
    const [savingCompany, setSavingCompany] = useState(false);

    const handleProfileSubmit = async (e) => {
        e.preventDefault();
        setSavingProfile(true);
        setProfileMsg("");
        setProfileErrors({});
        try {
            await getCsrfCookie();
            const res = await apiPost("/_auth/user/profile-information", {
                name,
                email,
                _method: "PUT",
            });
            if (res.ok || res.status === 200) {
                setProfileMsg("Profile updated successfully.");
                await fetchUser();
            } else {
                const data = await res.json().catch(() => ({}));
                if (data.errors) setProfileErrors(data.errors);
                else setProfileMsg(data.message || "Failed to update profile.");
            }
        } catch {
            setProfileMsg("An error occurred. Please try again.");
        } finally {
            setSavingProfile(false);
        }
    };

    const handleCompanySubmit = async (e) => {
        e.preventDefault();
        setSavingCompany(true);
        setCompanyMsg("");
        setCompanyErrors({});
        try {
            await getCsrfCookie();
            const formData = new FormData();
            formData.append("name", name);
            formData.append("email", email);
            formData.append("company_name", companyName);
            formData.append("slogan", slogan);
            if (logoFile) {
                formData.append("logo", logoFile);
            }

            const res = await apiPostFormData("/api/profile", formData);
            if (res.ok) {
                setCompanyMsg("Company information updated successfully.");
                await fetchUser();
            } else {
                const data = await res.json().catch(() => ({}));
                if (data.errors) setCompanyErrors(data.errors);
                else setCompanyMsg(data.message || "Failed to update company info.");
            }
        } catch {
            setCompanyMsg("An error occurred. Please try again.");
        } finally {
            setSavingCompany(false);
        }
    };

    const handleLogoChange = (e) => {
        const file = e.target.files?.[0];
        if (file) {
            setLogoFile(file);
            setLogoPreview(URL.createObjectURL(file));
        }
    };

    const handlePasswordSubmit = async (e) => {
        e.preventDefault();
        setSavingPassword(true);
        setPasswordMsg("");
        setPasswordErrors({});
        try {
            await getCsrfCookie();
            const res = await apiPost("/_auth/user/password", {
                current_password: currentPassword,
                password: newPassword,
                password_confirmation: confirmPassword,
                _method: "PUT",
            });
            if (res.ok || res.status === 200) {
                setPasswordMsg("Password changed successfully.");
                setCurrentPassword("");
                setNewPassword("");
                setConfirmPassword("");
            } else {
                const data = await res.json().catch(() => ({}));
                if (data.errors) setPasswordErrors(data.errors);
                else setPasswordMsg(data.message || "Failed to update password.");
            }
        } catch {
            setPasswordMsg("An error occurred. Please try again.");
        } finally {
            setSavingPassword(false);
        }
    };

    return (
        <>
            <Header1 headerClass="boxcar-header header-style-v1 style-two inner-header cus-style-1" />
            <section className="login-section layout-radius">
                <div className="boxcar-container">
                    <div className="row justify-content-center">
                        <div className="col-lg-8 col-md-10">
                            <div className="form-sec">
                                <h4 className="title mb-4">Profile Settings</h4>

                                <div className="form-box mb-5">
                                    <h6 className="mb-3">Personal Information</h6>
                                    <form onSubmit={handleProfileSubmit}>
                                        <div className="form_boxes">
                                            <label>Name</label>
                                            <input
                                                type="text"
                                                value={name}
                                                onChange={(e) => setName(e.target.value)}
                                                required
                                                disabled={savingProfile}
                                            />
                                            {profileErrors.name && (
                                                <small className="text-danger d-block mt-1">{profileErrors.name[0]}</small>
                                            )}
                                        </div>
                                        <div className="form_boxes">
                                            <label>Email</label>
                                            <input
                                                type="email"
                                                value={email}
                                                disabled
                                                style={{ opacity: 0.6 }}
                                            />
                                            <small className="text-muted d-block mt-1">Email cannot be changed.</small>
                                        </div>
                                        {user?.country && (
                                            <div className="form_boxes">
                                                <label>Country</label>
                                                <input type="text" value={user.country} disabled style={{ opacity: 0.6 }} />
                                            </div>
                                        )}
                                        {profileMsg && (
                                            <div className={`alert ${profileMsg.includes("success") ? "alert-success" : "alert-danger"} mb-3`}>
                                                {profileMsg}
                                            </div>
                                        )}
                                        <div className="form-submit">
                                            <button type="submit" className="theme-btn" disabled={savingProfile}>
                                                {savingProfile ? "Saving..." : "Save Changes"}
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <div className="form-box mb-5">
                                    <h6 className="mb-3">Company Branding</h6>
                                    <p className="text-muted mb-3" style={{ fontSize: "13px" }}>
                                        Used in offers, PDFs and emails sent to your clients.
                                    </p>
                                    <form onSubmit={handleCompanySubmit}>
                                        <div className="form_boxes">
                                            <label>Company Name</label>
                                            <input
                                                type="text"
                                                value={companyName}
                                                onChange={(e) => setCompanyName(e.target.value)}
                                                disabled={savingCompany}
                                                placeholder="Your company name"
                                            />
                                            {companyErrors.company_name && (
                                                <small className="text-danger d-block mt-1">{companyErrors.company_name[0]}</small>
                                            )}
                                        </div>
                                        <div className="form_boxes">
                                            <label>Slogan</label>
                                            <input
                                                type="text"
                                                value={slogan}
                                                onChange={(e) => setSlogan(e.target.value)}
                                                disabled={savingCompany}
                                                placeholder="Company slogan or tagline"
                                            />
                                            {companyErrors.slogan && (
                                                <small className="text-danger d-block mt-1">{companyErrors.slogan[0]}</small>
                                            )}
                                        </div>
                                        <div className="form_boxes">
                                            <label>Logo</label>
                                            <div className="d-flex align-items-center gap-3 mb-2">
                                                {logoPreview && (
                                                    <img
                                                        src={logoPreview}
                                                        alt="Company logo"
                                                        style={{
                                                            height: "60px",
                                                            width: "auto",
                                                            objectFit: "contain",
                                                            border: "1px solid #dee2e6",
                                                            borderRadius: "6px",
                                                            padding: "4px",
                                                        }}
                                                    />
                                                )}
                                                <div>
                                                    <input
                                                        ref={logoInputRef}
                                                        type="file"
                                                        accept="image/jpeg,image/png,image/gif,image/webp"
                                                        style={{ display: "none" }}
                                                        onChange={handleLogoChange}
                                                    />
                                                    <button
                                                        type="button"
                                                        className="btn btn-sm btn-outline-secondary"
                                                        onClick={() => logoInputRef.current?.click()}
                                                    >
                                                        {logoPreview ? "Change logo" : "Upload logo"}
                                                    </button>
                                                    <small className="text-muted d-block mt-1">
                                                        JPEG, PNG, GIF or WebP. Max 2MB.
                                                    </small>
                                                </div>
                                            </div>
                                            {companyErrors.logo && (
                                                <small className="text-danger d-block mt-1">{companyErrors.logo[0]}</small>
                                            )}
                                        </div>
                                        {companyMsg && (
                                            <div className={`alert ${companyMsg.includes("success") ? "alert-success" : "alert-danger"} mb-3`}>
                                                {companyMsg}
                                            </div>
                                        )}
                                        <div className="form-submit">
                                            <button type="submit" className="theme-btn" disabled={savingCompany}>
                                                {savingCompany ? "Saving..." : "Save Company Info"}
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <div className="form-box">
                                    <h6 className="mb-3">Change Password</h6>
                                    <form onSubmit={handlePasswordSubmit}>
                                        <div className="form_boxes">
                                            <label>Current Password</label>
                                            <input
                                                type="password"
                                                value={currentPassword}
                                                onChange={(e) => setCurrentPassword(e.target.value)}
                                                required
                                                disabled={savingPassword}
                                                placeholder="Enter current password"
                                            />
                                            {passwordErrors.current_password && (
                                                <small className="text-danger d-block mt-1">{passwordErrors.current_password[0]}</small>
                                            )}
                                        </div>
                                        <div className="form_boxes">
                                            <label>New Password</label>
                                            <input
                                                type="password"
                                                value={newPassword}
                                                onChange={(e) => setNewPassword(e.target.value)}
                                                required
                                                disabled={savingPassword}
                                                placeholder="Enter new password"
                                            />
                                            {passwordErrors.password && (
                                                <small className="text-danger d-block mt-1">{passwordErrors.password[0]}</small>
                                            )}
                                        </div>
                                        <div className="form_boxes">
                                            <label>Confirm New Password</label>
                                            <input
                                                type="password"
                                                value={confirmPassword}
                                                onChange={(e) => setConfirmPassword(e.target.value)}
                                                required
                                                disabled={savingPassword}
                                                placeholder="Confirm new password"
                                            />
                                        </div>
                                        {passwordMsg && (
                                            <div className={`alert ${passwordMsg.includes("success") ? "alert-success" : "alert-danger"} mb-3`}>
                                                {passwordMsg}
                                            </div>
                                        )}
                                        <div className="form-submit">
                                            <button type="submit" className="theme-btn" disabled={savingPassword}>
                                                {savingPassword ? "Changing..." : "Change Password"}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <Footer1 parentClass="boxcar-footer footer-style-one v1 cus-st-1" />
        </>
    );
}
