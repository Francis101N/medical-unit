 <?php
    $currentPage = basename($_SERVER['PHP_SELF']);
    ?>
 <div id="sidebar" class="active">
     <div class="sidebar-wrapper active">
         <div class="sidebar-header">
             <div class="d-flex justify-content-between">
                 <div class="logo">
                     <a href="index.php"><img src="assets/images/logo/Gemini_Generated_Image_mrlvttmrlvttmrlv-removebg-preview.png" alt="Logo" srcset="" style="width:100px; height: 80px;"></a>
                 </div>
                 <div class="toggler">
                     <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                 </div>
             </div>
         </div>
         <div class="sidebar-menu">
             <ul class="menu">
                 <li class="sidebar-title">Menu</li>

                 <li class="sidebar-item <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">
                     <a href="index.php" class="sidebar-link">
                         <i class="bi bi-grid-fill"></i>
                         <span>Dashboard</span>
                     </a>
                 </li>
                 <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['super-admin', 'staff'])): ?>
                     <?php if (strtolower($_SESSION['role'] ?? '') !== 'staff'): ?>
                         <li class="sidebar-item <?php echo ($currentPage == 'users.php') ? 'active' : ''; ?>">
                             <a href="users.php" class="sidebar-link">
                                 <i class="bi bi-person-badge-fill"></i>
                                 <span>Medical Admins</span>
                             </a>
                         </li>
                     <?php endif; ?>
                     <li class="sidebar-item <?php echo ($currentPage == 'staffs.php') ? 'active' : ''; ?>">
                         <a href="staffs.php" class="sidebar-link">
                             <i class="bi bi-people-fill"></i>
                             <span>Staffs</span>
                         </a>
                     </li>
                     <li class="sidebar-item <?php echo ($currentPage == 'branches.php') ? 'active' : ''; ?>">
                         <a href="branches.php" class="sidebar-link">
                             <i class="bi bi-diagram-3-fill"></i>
                             <span>Branches</span>
                         </a>
                     </li>
                     <li class="sidebar-item <?php echo ($currentPage == 'medical-records.php') ? 'active' : ''; ?>">
                         <a href="medical-records.php" class="sidebar-link">
                             <i class="bi bi-file-earmark-medical-fill"></i>
                             <span>Staff Medical Records</span>
                         </a>
                     </li>
                 <?php endif; ?>

                 <?php
                    // Ensure $currentPage is defined on every page before including sidebar (e.g. $currentPage = 'branch_drugs.php';)
                    $user_role = strtolower($_SESSION['role'] ?? '');
                    ?>

                 <!-- 1. Super-Admin Only: Branch Management -->
                 <?php if ($user_role === 'super-admin'): ?>
                     <li class="sidebar-item <?php echo ($currentPage == 'branch_records.php') ? 'active' : ''; ?>">
                         <a href="branch_records.php" class="sidebar-link">
                             <i class="bi bi-geo-alt-fill"></i>
                             <span>Branches Record</span>
                         </a>
                     </li>
                 <?php endif; ?>

                 <!-- 2. Super-Admin Only: Master Drug Catalog Definitions -->
                 <?php if ($user_role === 'super-admin'): ?>
                     <hr>INVENTORY MANAGEMENT
                     <li class="sidebar-item <?php echo ($currentPage == 'manage_drugs.php') ? 'active' : ''; ?>">
                         <a href="manage_drugs.php" class="sidebar-link">
                             <i class="bi bi-folder-plus"></i>
                             <span> Drug Catalog</span>
                         </a>
                     </li>
                     <li class="sidebar-item <?php echo ($currentPage == 'allocate_stock.php') ? 'active' : ''; ?>">
                         <a href="allocate_stock.php" class="sidebar-link">
                             <i class="bi bi-box-arrow-up-right"></i>
                             <span>Allocate Stock</span>
                         </a>
                     </li>
                 <?php endif; ?>

                 <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['super-admin', 'staff'])): ?>
                     <!-- 4. Visible to BOTH Super-Admin and Staff -->
                     <li class="sidebar-item <?php echo ($currentPage == 'branch_drugs.php') ? 'active' : ''; ?>">
                         <a href="branch_drugs.php" class="sidebar-link">
                             <i class="bi bi-archive"></i>
                             <span><?php echo ($user_role === 'super-admin') ? 'Global Drug Vaults' : 'Branch Drug Inventory'; ?></span>
                         </a>
                     </li>

                     <hr>REPORTS GENERATION
                     <li class="sidebar-item <?php echo ($currentPage == 'clinical-reports.php') ? 'active' : ''; ?>">
                         <a href="clinical-reports.php" class="sidebar-link">
                             <i class="bi bi-file-earmark-text-fill"></i>
                             <span>Staff Clinical Reports</span>
                         </a>
                     </li>
                     <?php if (strtolower($_SESSION['role'] ?? '') !== 'staff'): ?>
                         <li class="sidebar-item <?php echo ($currentPage == 'drugs_reports.php') ? 'active' : ''; ?>">
                             <a href="drugs_reports.php" class="sidebar-link">
                                 <i class="bi bi-file-earmark-text-fill"></i>
                                 <span>Drug Catalog Reports</span>
                             </a>
                         </li>
                     <?php endif; ?>
                     <li class="sidebar-item <?php echo ($currentPage == 'branch_drugs_reports.php') ? 'active' : ''; ?>">
                         <a href="branch_drugs_reports.php" class="sidebar-link">
                             <i class="bi bi-file-earmark-text-fill"></i>
                             <span>Branch Drug Reports</span>
                         </a>
                     </li>
                     <hr>REFERRAL LETTERS
                     <li class="sidebar-item <?php echo ($currentPage == 'referrals.php') ? 'active' : ''; ?>">
                         <a href="referrals.php" class="sidebar-link">
                             <i class="bi bi-file-medical-fill"></i>
                             <span>Referrals</span>
                         </a>
                     </li>
                 <?php endif; ?>
                 <hr>OUTREACH / CSR
                 <li class="sidebar-item <?php echo ($currentPage == 'outreach-overview.php') ? 'active' : ''; ?>">
                     <a href="outreach-overview.php" class="sidebar-link">
                         <i class="bi bi-grid-fill"></i>
                         <span>Outreach Overview</span>
                     </a>
                 </li>
                 <li class="sidebar-item <?php echo ($currentPage == 'outreach.php') ? 'active' : ''; ?>">
                     <a href="outreach.php" class="sidebar-link">
                         <i class="bi bi-heart-fill"></i>
                         <span>Outreach Locations</span>
                     </a>
                 </li>
                 <li class="sidebar-item <?php echo (in_array($currentPage, ['patient_medical_records.php', 'add_patient_medical_record.php', 'edit_patient_medical_record.php', 'view_patient_medical_record.php'])) ? 'active' : ''; ?>">
                     <a href="patient_medical_records.php" class="sidebar-link">
                         <i class="bi bi-file-medical-fill"></i>
                         <span>Patient Records</span>
                     </a>
                 </li>
                 <?php if (strtolower(trim($_SESSION['role'] ?? '')) === 'super-admin'): ?>
                     <li class="sidebar-item <?php echo (in_array($currentPage, ['outreach_inventory.php', 'add_outreach_inventory.php', 'edit_outreach_inventory.php', 'view_outreach_inventory.php'])) ? 'active' : ''; ?>">
                         <a href="outreach_inventory.php" class="sidebar-link">
                             <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16" style="display: inline-block; vertical-align: middle;">
                                 <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l5.504.223L13.14 3.5zM15 4.239l-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464z" />
                             </svg>
                             <span>Outreach Inventory</span>
                         </a>
                     </li>
                 <?php endif; ?>
                 <li class="sidebar-item <?php echo (in_array($currentPage, ['outreach_location_inventory.php', 'add_outreach_location_inventory.php', 'edit_outreach_location_inventory.php', 'view_outreach_location_inventory.php'])) ? 'active' : ''; ?>">
                     <a href="outreach_location_inventory.php" class="sidebar-link">
                         <i class="bi bi-shield-lock-fill"></i>
                         <span><?php echo ($user_role === 'adhoc-user') ? 'Current Drug Vault' : 'Global Drug Vaults'; ?></span>
                     </a>
                 </li>
                 <?php if (strtolower(trim($_SESSION['role'] ?? '')) === 'super-admin'): ?>
                     <li class="sidebar-item <?php echo (in_array($currentPage, ['outreach_locations_overview.php', 'outreach_location_medical_records.php'])) ? 'active' : ''; ?>">
                         <a href="outreach_locations_overview.php" class="sidebar-link">
                             <i class="bi bi-geo-alt-fill"></i>
                             <span>Locations Records</span>
                         </a>
                     </li>
                 <?php endif; ?>
                 <li class="sidebar-item <?php echo ($currentPage == 'patients_reports.php') ? 'active' : ''; ?>">
                     <a href="patients_reports.php" class="sidebar-link">
                         <i class="bi bi-file-earmark-text-fill"></i>
                         <span>Patients Reports</span>
                     </a>
                 </li>
                 <li class="sidebar-item">
                     <a href="logout.php"
                         class="sidebar-link"
                         onclick="return confirm('Are you sure you want to log out?');">
                         <i class="bi bi-box-arrow-right"></i>
                         <span>Logout</span>
                     </a>
                 </li>

             </ul>
         </div>
         <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
     </div>
 </div>