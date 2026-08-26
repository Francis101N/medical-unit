 <?php
    $currentPage = basename($_SERVER['PHP_SELF']);
    ?>
 <div id="sidebar" class="active">
     <div class="sidebar-wrapper active">
         <div class="sidebar-header">
             <div class="d-flex justify-content-between">
                 <div class="logo">
                     <a href="index.php"><img src="assets/images/logo/logo.png" alt="Logo" srcset="" style="width:100px; height: 80px;"></a>
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
                 <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'super-admin'): ?>
                     <li class="sidebar-item <?php echo ($currentPage == 'users.php') ? 'active' : ''; ?>">
                         <a href="users.php" class="sidebar-link">
                             <i class="bi bi-person-badge-fill"></i>
                             <span>Users / Admins</span>
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

                 <hr>INVENTORY MANAGEMENT

                 <!-- 2. Super-Admin Only: Master Drug Catalog Definitions -->
                 <?php if ($user_role === 'super-admin'): ?>
                     <li class="sidebar-item <?php echo ($currentPage == 'manage_drugs.php') ? 'active' : ''; ?>">
                         <a href="manage_drugs.php" class="sidebar-link">
                             <i class="bi bi-folder-plus"></i>
                             <span> Drug Catalog</span>
                         </a>
                     </li>
                 <?php endif; ?>

                 <!-- 3. Super-Admin Only: Stock Dispatch / Allocation Logic -->
                 <?php if ($user_role === 'super-admin'): ?>
                     <li class="sidebar-item <?php echo ($currentPage == 'allocate_stock.php') ? 'active' : ''; ?>">
                         <a href="allocate_stock.php" class="sidebar-link">
                             <i class="bi bi-box-arrow-up-right"></i>
                             <span>Allocate Stock</span>
                         </a>
                     </li>
                 <?php endif; ?>

                 <!-- 4. Visible to BOTH Super-Admin and Branch Staff -->
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
                 <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'super-admin'): ?>
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
                 <hr>OUTREACH / CSR
                 <li class="sidebar-item <?php echo ($currentPage == 'outreach.php') ? 'active' : ''; ?>">
                     <a href="outreach.php" class="sidebar-link">
                         <i class="bi bi-heart-fill"></i>
                         <span>Outreach</span>
                     </a>
                 </li>
                 <li class="sidebar-item <?php echo (in_array($currentPage, ['patient_medical_records.php', 'add_patient_medical_record.php', 'edit_patient_medical_record.php', 'view_patient_medical_record.php'])) ? 'active' : ''; ?>">
                     <a href="patient_medical_records.php" class="sidebar-link">
                         <i class="bi bi-file-medical-fill"></i>
                         <span>Patient Medical Records</span>
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