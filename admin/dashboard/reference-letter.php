<?php
// medical_referral_letter.php
// Standalone web page to generate, preview, download, and print corporate medical referral letters with logo upload support.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corporate Medical Referral Letter Generator</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
        }

        .form-card,
        .preview-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 24px;
        }

        /* Letter Print & Preview Styling */
        .letter-document {
            background: #ffffff;
            border: 1px solid #dee2e6;
            padding: 40px;
            min-height: 700px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            line-height: 1.6;
            color: #000;
        }

        .letterhead-logo-preview {
            max-height: 60px;
            max-width: 180px;
            object-fit: contain;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #printableLetter,
            #printableLetter * {
                visibility: visible;
            }

            #printableLetter {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none;
                box-shadow: none;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <!-- Page Header -->
        <div class="row mb-4 no-print">
            <div class="col-12">
                <h2 class="fw-bold text-dark">Medical Referral Letter Generator</h2>
                <p class="text-muted">Fill out the clinical and staff profile fields or upload your company logo below to instantly generate, print, or download a professional corporate medical referral letter.</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Input Form Column -->
            <div class="col-12 col-lg-5 no-print">
                <div class="form-card">
                    <h5 class="fw-bold mb-3 text-primary">Referral Information</h5>
                    <form id="referralForm">
                        <!-- Company Logo Upload -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Company Logo</label>
                            <input type="file" class="form-control form-control-sm" id="logoInput" accept="image/*">
                            <div class="form-text text-muted" style="font-size: 0.75rem;">Upload PNG or JPG image for the letterhead header.</div>
                        </div>

                        <!-- Company & Medical Unit Info -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Company / Medical Department Name</label>
                            <input type="text" class="form-control form-control-sm" id="companyName" value="Equal Logistics Limited" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Company Address / Contact Info</label>
                            <input type="text" class="form-control form-control-sm" id="companyAddress" value="11A, Tokunbo Omisore Street, Off Admiralty Way, Lekki Phase 1, Lagos, Nigeria. TEL: +234 901 072 1580 or 0905 549 3503" required>
                        </div>
                        <hr class="text-muted">

                        <!-- Hospital Target Info -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Target Hospital Name</label>
                            <input type="text" class="form-control form-control-sm" id="hospitalName" value="St. Nicholas Hospital" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Hospital Address</label>
                            <input type="text" class="form-control form-control-sm" id="hospitalAddress" value="57 Campbell Street, Lagos Island, Lagos" required>
                        </div>
                        <hr class="text-muted">

                        <!-- Staff Details -->
                        <div class="row g-2 mb-3">
                            <div class="col-8">
                                <label class="form-label fw-semibold small">Staff Full Name</label>
                                <input type="text" class="form-control form-control-sm" id="staffName" value="Oluwaseun Adebayo" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold small">Staff ID</label>
                                <input type="text" class="form-control form-control-sm" id="staffId" value="HC-8492" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Job Title</label>
                                <input type="text" class="form-control form-control-sm" id="jobTitle" value="Senior Software Engineer" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Department</label>
                                <input type="text" class="form-control form-control-sm" id="department" value="Engineering Division" required>
                            </div>
                        </div>

                        <!-- Clinical / Medical Info -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Clinical Findings / Symptoms</label>
                            <textarea class="form-control form-control-sm" id="clinicalFindings" rows="2" required>persistent acute lower back strain and associated physical discomfort</textarea>
                        </div>

                        <!-- Insurance Info -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Insurance Plan & Policy Number</label>
                            <input type="text" class="form-control form-control-sm" id="insuranceDetails" value="Reliance HMO / Corporate Plan #RH-99420" required>
                        </div>

                        <!-- Physician Info -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Physician Name</label>
                                <input type="text" class="form-control form-control-sm" id="doctorName" value="Mrs. Grace " required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Contact Phone / Email</label>
                                <input type="text" class="form-control form-control-sm" id="doctorContact" value="medical@healthcorp.net" required>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 mt-4">
                            <button type="button" class="btn btn-primary btn-sm fw-bold" id="printBtn">Print / Save as PDF</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="downloadTxtBtn">Download as Text File</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Live Letter Preview Column -->
            <div class="col-12 col-lg-7">
                <div class="preview-card">
                    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
                        <h5 class="fw-bold text-secondary mb-0">Live Document Preview</h5>
                        <span class="badge bg-success">Ready</span>
                    </div>

                    <!-- Printable Letter Container -->
                    <div id="printableLetter" class="letter-document">
                        <!-- Letterhead Header with Logo Space -->
                        <div class="row align-items-center mb-4 pb-3 border-bottom">
                            <div class="col-8">
                                <h4 id="prevCompanyName" class="fw-bold mb-1">[Company Medical Department]</h4>
                                <div id="prevCompanyAddress" class="text-muted small">[Company Address & Contact]</div>
                            </div>
                            <div class="col-4 text-end">
                                <div id="logoContainer" class="d-inline-block">
                                    <!-- Dynamic Logo Placeholder or Image -->
                                    <div id="logoPlaceholder" class="border border-secondary border-dashed p-2 text-muted small rounded" style="font-size: 0.75rem; background: #fafafa; min-width: 100px; display: inline-block;">
                                        [Company Logo]
                                    </div>
                                    <img id="logoImageDisplay" class="letterhead-logo-preview d-none" alt="Company Logo">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <strong>Date:</strong> <span id="prevDate"></span>
                        </div>

                        <!-- Recipient Address -->
                        <div class="mb-4">
                            <strong>To:</strong><br>
                            The Medical Director,<br>
                            <span id="prevHospitalName">[Hospital Name]</span><br>
                            <span id="prevHospitalAddress">[Hospital Address]</span>
                        </div>

                        <!-- Subject -->
                        <div class="mb-4 fw-bold">
                            Subject: Medical Referral & Staff Verification: <span id="prevStaffNameHeader">[Staff Full Name]</span> (Staff ID: <span id="prevStaffIdHeader">[Insert ID]</span>)
                        </div>

                        <!-- Salutation -->
                        <div class="mb-3">
                            Dear Medical Team,
                        </div>

                        <!-- Body Paragraphs -->
                        <div class="mb-3">
                            This letter serves as a formal medical referral and professional reference for our employee, <strong><span id="prevStaffName">[Staff Full Name]</span></strong> (Staff ID: <strong><span id="prevStaffId">[Insert ID]</span></strong>), who serves as a <strong><span id="prevJobTitle">[Job Title]</span></strong> in our <strong><span id="prevDepartment">[Department Name]</span></strong> division.
                        </div>

                        <div class="mb-3">
                            Following an evaluation by our on-site medical team, <span id="prevStaffFirstName">[Staff First Name]</span> requires specialized clinical assessment and management for <strong><span id="prevClinicalFindings">[Briefly state condition/symptoms]</span></strong>.
                        </div>

                        <div class="mb-3">
                            We kindly request that you provide the necessary diagnostic and treatment services. The employee is covered under our corporate health insurance plan: <strong><span id="prevInsurance">[Insurance Provider / Policy Number]</span></strong>.
                        </div>

                        <div class="mb-4">
                            Please forward all official medical reports and treatment updates to our corporate medical desk. For any employment or insurance verifications, contact us directly at <strong><span id="prevDoctorContact">[Phone Number / Email]</span></strong>.
                        </div>

                        <div class="mb-4">
                            Thank you for your prompt professional assistance.
                        </div>

                        <!-- Sign-off -->
                        <div class="mt-4">
                            Sincerely,<br><br><br>
                            <strong><span id="prevDoctorName">Dr. [Doctor's Full Name]</span></strong><br>
                            Head of Medical Services, <span id="prevCompanySign">[Company Name]</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Real-time Binding & Controls -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Set default date formatted
            const today = new Date().toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById('prevDate').textContent = today;

            // Logo Upload Handler
            const logoInput = document.getElementById('logoInput');
            const logoPlaceholder = document.getElementById('logoPlaceholder');
            const logoImageDisplay = document.getElementById('logoImageDisplay');

            logoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        logoImageDisplay.src = event.target.result;
                        logoImageDisplay.classList.remove('d-none');
                        logoPlaceholder.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                } else {
                    logoImageDisplay.classList.add('d-none');
                    logoPlaceholder.style.display = 'inline-block';
                }
            });

            // Form Fields Elements
            const fields = {
                companyName: document.getElementById('companyName'),
                companyAddress: document.getElementById('companyAddress'),
                hospitalName: document.getElementById('hospitalName'),
                hospitalAddress: document.getElementById('hospitalAddress'),
                staffName: document.getElementById('staffName'),
                staffId: document.getElementById('staffId'),
                jobTitle: document.getElementById('jobTitle'),
                department: document.getElementById('department'),
                clinicalFindings: document.getElementById('clinicalFindings'),
                insuranceDetails: document.getElementById('insuranceDetails'),
                doctorName: document.getElementById('doctorName'),
                doctorContact: document.getElementById('doctorContact')
            };

            // Preview Targets Elements
            const targets = {
                prevCompanyName: document.getElementById('prevCompanyName'),
                prevCompanyAddress: document.getElementById('prevCompanyAddress'),
                prevHospitalName: document.getElementById('prevHospitalName'),
                prevHospitalAddress: document.getElementById('prevHospitalAddress'),
                prevStaffNameHeader: document.getElementById('prevStaffNameHeader'),
                prevStaffIdHeader: document.getElementById('prevStaffIdHeader'),
                prevStaffName: document.getElementById('prevStaffName'),
                prevStaffFirstName: document.getElementById('prevStaffFirstName'),
                prevStaffId: document.getElementById('prevStaffId'),
                prevJobTitle: document.getElementById('prevJobTitle'),
                prevDepartment: document.getElementById('prevDepartment'),
                prevClinicalFindings: document.getElementById('prevClinicalFindings'),
                prevInsurance: document.getElementById('prevInsurance'),
                prevDoctorContact: document.getElementById('prevDoctorContact'),
                prevDoctorName: document.getElementById('prevDoctorName'),
                prevCompanySign: document.getElementById('prevCompanySign')
            };

            function updatePreview() {
                const cName = fields.companyName.value;
                const hName = fields.hospitalName.value;
                const hAddr = fields.hospitalAddress.value;
                const sName = fields.staffName.value;
                const sId = fields.staffId.value;
                const jTitle = fields.jobTitle.value;
                const dept = fields.department.value;
                const cFindings = fields.clinicalFindings.value;
                const ins = fields.insuranceDetails.value;
                const dName = fields.doctorName.value;
                const dContact = fields.doctorContact.value;

                // Extract first name for dynamic reference
                const sFirstName = sName.trim().split(' ')[0] || sName;

                targets.prevCompanyName.textContent = cName;
                targets.prevCompanyAddress.textContent = fields.companyAddress.value;
                targets.prevHospitalName.textContent = hName;
                targets.prevHospitalAddress.textContent = hAddr;
                targets.prevStaffNameHeader.textContent = sName;
                targets.prevStaffIdHeader.textContent = sId;
                targets.prevStaffName.textContent = sName;
                targets.prevStaffFirstName.textContent = sFirstName;
                targets.prevStaffId.textContent = sId;
                targets.prevJobTitle.textContent = jTitle;
                targets.prevDepartment.textContent = dept;
                targets.prevClinicalFindings.textContent = cFindings;
                targets.prevInsurance.textContent = ins;
                targets.prevDoctorContact.textContent = dContact;
                targets.prevDoctorName.textContent = dName;
                targets.prevCompanySign.textContent = cName;
            }

            // Attach input event listeners to all form fields
            Object.values(fields).forEach(input => {
                input.addEventListener('input', updatePreview);
            });

            // Initial render call
            updatePreview();

            // Print Trigger
            document.getElementById('printBtn').addEventListener('click', function() {
                window.print();
            });

            // Download Text File Trigger
            document.getElementById('downloadTxtBtn').addEventListener('click', function() {
                const letterContent = document.getElementById('printableLetter').innerText;
                const blob = new Blob([letterContent], {
                    type: 'text/plain;charset=utf-8'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `Medical_Referral_${fields.staffName.value.replace(/\s+/g, '_')}.txt`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            });
        });
    </script>
</body>

</html>