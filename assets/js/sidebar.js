document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("sidebar");
  const content = document.getElementById("content");
  const sidebarCollapse = document.getElementById("sidebarCollapse");
  const navLinks = document.querySelectorAll(".nav-link");

  // Toggle sidebar
  sidebarCollapse.addEventListener("click", function () {
    sidebar.classList.toggle("collapsed");
    content.classList.toggle("expanded");
  });

  // Active link
  navLinks.forEach((link) => {
    link.addEventListener("click", function () {
      // Remove active class from all links
      navLinks.forEach((l) => l.classList.remove("active"));
      // Add active class to clicked link
      this.classList.add("active");
    });
  });

  // Set active link based on current page
  const currentPage = window.location.pathname;
  navLinks.forEach((link) => {
    if (link.getAttribute("href") === currentPage) {
      link.classList.add("active");
    }
  });

  // Mobile sidebar
  if (window.innerWidth <= 768) {
    sidebar.classList.add("collapsed");
    content.classList.add("expanded");
  }

  // Handle window resize
  window.addEventListener("resize", function () {
    if (window.innerWidth <= 768) {
      sidebar.classList.add("collapsed");
      content.classList.add("expanded");
    }
  });

  // Add hover effect to dropdown items
  const dropdownItems = document.querySelectorAll(".dropdown-item");
  dropdownItems.forEach((item) => {
    item.addEventListener("mouseenter", function () {
      this.style.transform = "translateX(5px)";
    });
    item.addEventListener("mouseleave", function () {
      this.style.transform = "translateX(0)";
    });
  });
});
