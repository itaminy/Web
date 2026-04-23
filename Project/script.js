document.addEventListener("DOMContentLoaded", function () {
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle");
  const mobileMenu = document.querySelector(".mobile-menu");
  const mobileClose = document.querySelector(".mobile-close");
  const mobileDropdowns = document.querySelectorAll(".mobile-dropdown");
  const navLinks = document.querySelectorAll('a[href^="#"]');
  const mainForm = document.getElementById("mainForm");
  const loginForm = document.getElementById("loginFormElement");
  const editForm = document.getElementById("editFormElement");

  let currentUserId = null;
  let isLoggedIn = false;

  // Базовый путь (если сайт в подпапке)
  const BASE_PATH = "/Web/Project";

  function initMobileMenu() {
    if (mobileMenuToggle && mobileMenu) {
      mobileMenuToggle.addEventListener("click", function (e) {
        e.stopPropagation();
        toggleMobileMenu();
      });
      if (mobileClose) {
        mobileClose.addEventListener("click", function () { closeMobileMenu(); });
      }
      mobileDropdowns.forEach((dropdown) => {
        const header = dropdown.querySelector(".mobile-dropdown-header");
        if (header) {
          header.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            const isActive = dropdown.classList.contains("active");
            mobileDropdowns.forEach((other) => {
              if (other !== dropdown) other.classList.remove("active");
            });
            dropdown.classList.toggle("active", !isActive);
          });
        }
      });
      document.addEventListener("click", function (e) {
        if (mobileMenu && mobileMenuToggle && !mobileMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
          closeMobileMenu();
        }
      });
    }
  }

  function toggleMobileMenu() {
    if (mobileMenu.classList.contains("active")) {
      closeMobileMenu();
    } else {
      openMobileMenu();
    }
  }

  function openMobileMenu() {
    mobileMenu.classList.add("active");
    if (mobileMenuToggle) mobileMenuToggle.classList.add("active");
    document.body.classList.add("menu-open");
    const overlay = document.createElement("div");
    overlay.className = "mobile-menu-overlay";
    overlay.style.cssText = `position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1999;animation:fadeIn 0.3s ease;`;
    overlay.addEventListener("click", closeMobileMenu);
    document.body.appendChild(overlay);
  }

  function closeMobileMenu() {
    mobileMenu.classList.remove("active");
    if (mobileMenuToggle) mobileMenuToggle.classList.remove("active");
    document.body.classList.remove("menu-open");
    const overlay = document.querySelector(".mobile-menu-overlay");
    if (overlay) overlay.remove();
    mobileDropdowns.forEach((dropdown) => { dropdown.classList.remove("active"); });
  }

  function initSmoothScroll() {
    navLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        const targetId = this.getAttribute("href");
        if (targetId === "#" || !targetId.startsWith("#")) return;
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          e.preventDefault();
          const headerHeight = document.querySelector(".navbar")?.offsetHeight || 80;
          const targetPosition = targetElement.offsetTop - headerHeight - 20;
          window.scrollTo({ top: targetPosition, behavior: "smooth" });
          if (mobileMenu && mobileMenu.classList.contains("active")) { closeMobileMenu(); }
        }
      });
    });
  }

  function showNotification(message, type = "success") {
    const existing = document.querySelector(".notification");
    if (existing) existing.remove();
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `position:fixed;top:20px;right:20px;background:${type === "success" ? "#4CAF50" : type === "error" ? "#f14d34" : "#2196F3"};color:white;padding:15px 20px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.15);z-index:10000;max-width:400px;animation:slideInRight 0.3s ease-out;`;
    notification.innerHTML = `<div class="notification-content"><span class="notification-message">${message}</span><button class="notification-close" style="margin-left:15px;background:none;border:none;color:white;font-size:20px;cursor:pointer;">&times;</button></div>`;
    document.body.appendChild(notification);
    const closeBtn = notification.querySelector(".notification-close");
    closeBtn.addEventListener("click", () => { notification.remove(); });
    setTimeout(() => { if (notification.parentNode) notification.remove(); }, 5000);
  }

  function showCredentialsModal(login, password) {
    const modal = document.getElementById("credentialsModal");
    const content = document.getElementById("credentialsContent");
    if (modal && content) {
      content.innerHTML = `<div class="login-credentials"><p><strong>✅ Вы успешно зарегистрированы!</strong></p><p>🔐 <strong>Ваши данные для входа:</strong></p><p>Логин: <code>${login}</code></p><p>Пароль: <code>${password}</code></p><p><small>⚠️ Сохраните эти данные! Они понадобятся для входа в личный кабинет.</small></p></div>`;
      modal.classList.add("active");
    }
  }

  function closeCredentialsModal() { const modal = document.getElementById("credentialsModal"); if (modal) modal.classList.remove("active"); }
  function showLoginModal() { const modal = document.getElementById("loginModal"); if (modal) modal.classList.add("active"); }
  function closeLoginModal() { const modal = document.getElementById("loginModal"); if (modal) modal.classList.remove("active"); }
  function closeEditModal() { const modal = document.getElementById("editModal"); if (modal) modal.classList.remove("active"); }

  function showEditModal() {
    if (!isLoggedIn || !currentUserId) {
      showNotification("Пожалуйста, сначала войдите в систему", "error");
      return;
    }
    fetch(`${BASE_PATH}/api.php?action=get&id=${currentUserId}`, { 
      method: "GET", 
      credentials: "same-origin", 
      headers: { "Accept": "application/json" } 
    })
      .then(res => res.json())
      .then(data => {
        if (data.error) { showNotification(data.error, "error"); return; }
        document.getElementById("edit_user_id").value = data.id;
        document.getElementById("edit_full_name").value = data.full_name || "";
        document.getElementById("edit_phone").value = data.phone || "";
        document.getElementById("edit_email").value = data.email || "";
        document.getElementById("edit_birth_date").value = data.birth_date || "";
        document.getElementById("edit_gender").value = data.gender || "male";
        const langSelect = document.getElementById("edit_languages");
        if (langSelect && data.languages) {
          Array.from(langSelect.options).forEach(opt => { opt.selected = data.languages.includes(opt.value); });
        }
        document.getElementById("edit_biography").value = data.biography || "";
        document.getElementById("editModal").classList.add("active");
      })
      .catch(() => { showNotification("Ошибка загрузки данных", "error"); });
  }

  function updateAuthButtons() {
    const authContainer = document.getElementById("authButtons");
    if (!authContainer) return;
    if (isLoggedIn) {
      authContainer.innerHTML = `<div class="user-info"><span>👤 ${sessionStorage.getItem("user_login") || "Пользователь"}</span><button class="edit-profile-btn" onclick="window.editProfile()">Редактировать</button><button class="logout-btn" onclick="window.logoutUser()">Выйти</button></div>`;
    } else {
      authContainer.innerHTML = `<button class="login-btn" onclick="window.showLogin()">Войти</button>`;
    }
  }

  window.showLogin = showLoginModal;
  window.editProfile = showEditModal;
  window.logoutUser = function() {
    fetch(`${BASE_PATH}/logout.php`, { method: "GET", credentials: "same-origin" })
      .then(() => { sessionStorage.clear(); isLoggedIn = false; currentUserId = null; updateAuthButtons(); showNotification("Вы вышли из системы", "info"); setTimeout(() => location.reload(), 1500); })
      .catch(() => { sessionStorage.clear(); isLoggedIn = false; currentUserId = null; updateAuthButtons(); location.reload(); });
  };

  function checkAuthStatus() {
    fetch(`${BASE_PATH}/check-auth.php`, { method: "GET", credentials: "same-origin", headers: { "Accept": "application/json" } })
      .then(res => res.json())
      .catch(() => ({}))
      .then(data => {
        if (data && data.logged_in) {
          isLoggedIn = true;
          currentUserId = data.user_id;
          sessionStorage.setItem("user_login", data.login);
          updateAuthButtons();
        } else {
          isLoggedIn = false;
          currentUserId = null;
          updateAuthButtons();
        }
      });
  }

  async function submitForm(formData) {
    // Правильный сбор выбранных языков из select
    const languagesSelect = document.getElementById("languages");
    const selectedLanguages = [];
    if (languagesSelect) {
        for (let i = 0; i < languagesSelect.options.length; i++) {
            if (languagesSelect.options[i].selected) {
                selectedLanguages.push(languagesSelect.options[i].value);
            }
        }
    }
    
    const formObject = {
        full_name: formData.get("full_name") || document.getElementById("full_name")?.value || "",
        phone: formData.get("phone") || document.getElementById("phone")?.value || "",
        email: formData.get("email") || document.getElementById("email")?.value || "",
        birth_date: formData.get("birth_date") || document.getElementById("birth_date")?.value || "",
        gender: formData.get("gender") || document.getElementById("gender")?.value || "",
        languages: selectedLanguages,
        biography: formData.get("biography") || document.getElementById("biography")?.value || "",
        contract: (formData.get("contract") || document.getElementById("contract")?.checked) ? "1" : ""
    };
    
    console.log("📤 Отправляемые данные:", formObject);  // Отладка в консоли
    
    try {
        const response = await fetch(`${BASE_PATH}/api.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json", "Accept": "application/json" },
            credentials: "same-origin",
            body: JSON.stringify(formObject)
        });
        const result = await response.json();
        console.log("📥 Ответ сервера:", result);  // Отладка в консоли
        
        if (response.ok && result.success) {
            showNotification("Регистрация успешна!", "success");
            showCredentialsModal(result.login, result.password);
            if (mainForm) mainForm.reset();
            return true;
        } else {
            if (result.errors) {
                let errorMsg = "";
                for (const [key, value] of Object.entries(result.errors)) {
                    errorMsg += `${value}\n`;
                    const field = document.getElementById(key);
                    if (field) {
                        field.style.borderColor = "#f14d34";
                    }
                }
                showNotification(errorMsg, "error");
            } else {
                showNotification(result.error || "Ошибка регистрации", "error");
            }
            return false;
        }
    } catch (err) {
        console.error("Fetch error:", err);
        showNotification("Ошибка соединения. Форма отправлена обычным способом.", "error");
        if (mainForm) {
            mainForm.removeEventListener("submit", handleSubmit);
            mainForm.submit();
        }
        return false;
    }
}

  async function submitLogin(login, password) {
    try {
      const response = await fetch(`${BASE_PATH}/login.php`, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        credentials: "same-origin",
        body: new URLSearchParams({ login: login, password: password })
      });
      if (response.redirected || response.ok) {
        showNotification("Вход выполнен успешно!", "success");
        closeLoginModal();
        setTimeout(() => location.reload(), 1000);
        return true;
      } else {
        showNotification("Неверный логин или пароль", "error");
        return false;
      }
    } catch (err) {
      showNotification("Ошибка входа", "error");
      return false;
    }
  }

  async function submitEdit(userId, formData) {
    const formObject = {
      full_name: formData.get("full_name"),
      phone: formData.get("phone"),
      email: formData.get("email"),
      birth_date: formData.get("birth_date"),
      gender: formData.get("gender"),
      languages: formData.getAll("languages"),
      biography: formData.get("biography") || ""
    };
    try {
      const response = await fetch(`${BASE_PATH}/api.php?action=update&id=${userId}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json", "Accept": "application/json" },
        credentials: "same-origin",
        body: JSON.stringify(formObject)
      });
      const result = await response.json();
      if (response.ok && result.success) {
        showNotification("Данные обновлены!", "success");
        closeEditModal();
        return true;
      } else {
        if (result.errors) {
          let errorMsg = "";
          for (const [key, value] of Object.entries(result.errors)) {
            errorMsg += `${value}\n`;
          }
          showNotification(errorMsg, "error");
        } else {
          showNotification(result.error || "Ошибка обновления", "error");
        }
        return false;
      }
    } catch (err) {
      showNotification("Ошибка соединения", "error");
      return false;
    }
  }

  // Обработчик для формы
  async function handleSubmit(e) {
    e.preventDefault();
    const formData = new FormData(mainForm);
    await submitForm(formData);
  }

  if (mainForm) {
    mainForm.addEventListener("submit", handleSubmit);
  }

  if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const login = document.getElementById("login_username").value;
      const password = document.getElementById("login_password").value;
      await submitLogin(login, password);
    });
  }

  if (editForm) {
    editForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const userId = document.getElementById("edit_user_id").value;
      const formData = new FormData(editForm);
      await submitEdit(userId, formData);
    });
  }

  function initScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add("fade-in-up"); observer.unobserve(entry.target); } });
    }, { threshold: 0.1 });
    document.querySelectorAll(".service-card, .feature-card, .pricing-card, .task-card, .team-member, .case-card, .client-logo, .faq-item").forEach(el => observer.observe(el));
  }

  function initStickyNav() {
    const navbar = document.querySelector(".navbar");
    window.addEventListener("scroll", () => { if (navbar) { if (window.scrollY > 100) navbar.classList.add("scrolled"); else navbar.classList.remove("scrolled"); } });
  }

  function initCounters() {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const counter = entry.target;
          const target = parseInt(counter.textContent);
          let count = 0;
          const updateCount = () => { const increment = target / 200; if (count < target) { count += increment; counter.textContent = Math.ceil(count); setTimeout(updateCount, 1); } else { counter.textContent = target; } };
          updateCount();
          observer.unobserve(counter);
        }
      });
    }, { threshold: 0.5 });
    document.querySelectorAll(".stat-number").forEach(counter => observer.observe(counter));
  }

  initMobileMenu();
  initSmoothScroll();
  initScrollAnimations();
  initStickyNav();
  initCounters();
  checkAuthStatus();

  document.querySelectorAll(".pricing-card button").forEach(btn => {
    btn.addEventListener("click", (e) => { e.stopPropagation(); document.querySelector(".contact-form").scrollIntoView({ behavior: "smooth" }); });
  });
});
