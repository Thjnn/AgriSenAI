<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AgriSenAi — Sàn nông sản thông minh Đồng Tháp</title>
  <meta name="description" content="Sàn thương mại điện tử kết nối trực tiếp nông dân, hộ OCOP Đồng Tháp với người mua — tích hợp AI đánh giá chất lượng nông sản qua ảnh chụp và khuyến nghị thời điểm bán.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    *,
    *::before,
    *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --green: #58CC02;
      --green-dark: #46A302;
      --green-deeper: #2B8700;
      --green-light: #E8F8D4;
      --green-pale: #F0FBDF;
      --yellow: #FFC800;
      --yellow-light: #FFF4CC;
      --orange: #FF9600;
      --red-soft: #FF4B4B;
      --white: #FFFFFF;
      --off-white: #F7F7F7;
      --gray-100: #E5E5E5;
      --gray-200: #CDCDCD;
      --text: #3C3C3C;
      --text-muted: #777777;
      --radius: 16px;
      --radius-lg: 24px;
      --shadow-btn: 0 4px 0 var(--green-dark);
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Nunito', system-ui, sans-serif;
      color: var(--text);
      background: var(--white);
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
    }

    /* ============ UTILITY ============ */
    .container {
      max-width: 1140px;
      margin: 0 auto;
      padding: 20px 24px;
    }

    .btn-3d {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 14px 32px;
      border: none;
      border-radius: 16px;
      font-family: 'Nunito', sans-serif;
      font-size: 16px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      cursor: pointer;
      transition: all 0.15s;
      text-decoration: none;
      position: relative;
    }

    .btn-3d:active {
      transform: translateY(3px);
    }

    .btn-primary {
      background: var(--green);
      color: var(--white);
      box-shadow: 0 5px 0 var(--green-dark);
    }

    .btn-primary:active {
      box-shadow: 0 2px 0 var(--green-dark);
    }

    .btn-secondary {
      background: var(--white);
      color: var(--green);
      border: 2.5px solid var(--gray-200);
      box-shadow: 0 5px 0 var(--gray-200);
    }

    .btn-secondary:active {
      box-shadow: 0 2px 0 var(--gray-200);
    }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      color: var(--green);
      margin-bottom: 12px;
    }

    .section-title {
      font-size: clamp(28px, 5vw, 42px);
      font-weight: 900;
      line-height: 1.15;
      margin-bottom: 16px;
    }

    .section-desc {
      font-size: 17px;
      line-height: 1.7;
      color: var(--text-muted);
      max-width: 520px;
    }

    /* ============ NAV ============ */
    .nav {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 100;
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(12px);
      border-bottom: 2px solid var(--gray-100);
      transition: box-shadow 0.3s;
    }

    .nav.scrolled {
      box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
    }

    .nav-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 68px;
    }

    .nav-logo {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
    }

    .nav-logo-icon {
      width: 40px;
      height: 40px;
      background: var(--green);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      box-shadow: 0 3px 0 var(--green-dark);
    }

    .nav-logo-text {
      font-size: 22px;
      font-weight: 900;
      color: var(--text);
    }

    .nav-logo-text span {
      color: var(--green);
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 32px;
      list-style: none;
    }

    .nav-links a {
      text-decoration: none;
      color: var(--text);
      font-weight: 700;
      font-size: 15px;
      transition: color 0.2s;
    }

    .nav-links a:hover {
      color: var(--green);
    }

    .nav-cta {
      padding: 10px 24px !important;
      font-size: 14px !important;
    }

    .nav-mobile-toggle {
      display: none;
      background: none;
      border: none;
      cursor: pointer;
      width: 36px;
      height: 36px;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 5px;
    }

    .nav-mobile-toggle span {
      display: block;
      width: 24px;
      height: 2.5px;
      background: var(--text);
      border-radius: 2px;
      transition: all 0.3s;
    }

    /* ============ HERO ============ */
    .hero {
      padding-top: 120px;
      padding-bottom: 80px;
      min-height: 100vh;
      display: flex;
      align-items: center;
      overflow: hidden;
      position: relative;
    }

    .hero-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 60px;
      align-items: center;
    }

    .hero-content {
      position: relative;
      z-index: 2;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--green-light);
      padding: 8px 16px;
      border-radius: 100px;
      font-size: 14px;
      font-weight: 700;
      color: var(--green-deeper);
      margin-bottom: 24px;
    }

    .hero-badge-dot {
      width: 8px;
      height: 8px;
      background: var(--green);
      border-radius: 50%;
      animation: pulse-dot 2s ease infinite;
    }

    @keyframes pulse-dot {

      0%,
      100% {
        opacity: 1;
        transform: scale(1);
      }

      50% {
        opacity: 0.5;
        transform: scale(1.3);
      }
    }

    .hero-title {
      font-size: clamp(36px, 5.5vw, 56px);
      font-weight: 900;
      line-height: 1.1;
      margin-bottom: 20px;
    }

    .hero-title .highlight {
      color: var(--green);
      position: relative;
    }

    .hero-subtitle {
      font-size: 18px;
      line-height: 1.7;
      color: var(--text-muted);
      max-width: 480px;
      margin-bottom: 36px;
    }

    .hero-actions {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
      margin-bottom: 48px;
    }

    .hero-stats {
      display: flex;
      gap: 40px;
    }

    .hero-stat-num {
      font-size: 28px;
      font-weight: 900;
      color: var(--green);
    }

    .hero-stat-label {
      font-size: 13px;
      color: var(--text-muted);
      font-weight: 600;
      margin-top: 2px;
    }

    /* Hero visual - floating UI card composition */
    .hero-visual {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 480px;
    }

    .card-stack {
      position: relative;
      width: 440px;
      height: 440px;
    }

    .float-blob {
      position: absolute;
      inset: 46px;
      border-radius: 50%;
      background: radial-gradient(circle, var(--green-light) 0%, rgba(232, 248, 212, 0) 70%);
      z-index: 0;
      animation: blobPulse 5s ease-in-out infinite;
    }

    @keyframes blobPulse {

      0%,
      100% {
        transform: scale(1);
        opacity: 0.9;
      }

      50% {
        transform: scale(1.08);
        opacity: 0.6;
      }
    }

    .float-card {
      position: absolute;
      background: var(--white);
      border-radius: var(--radius-lg);
      border: 2.5px solid var(--gray-100);
      box-shadow: 0 6px 0 var(--gray-100);
      padding: 14px 16px;
      transition: transform 0.25s;
      will-change: transform;
    }

    .float-card-top {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .float-emoji-box {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      flex-shrink: 0;
      background: #FFF3D6;
    }

    .float-card-title {
      font-size: 14px;
      font-weight: 800;
      line-height: 1.25;
    }

    .float-card-meta {
      font-size: 11.5px;
      color: var(--text-muted);
      font-weight: 700;
      margin-top: 2px;
    }

    .float-card--listing {
      top: 6px;
      left: 0;
      width: 224px;
      transform: rotate(-7deg);
      z-index: 3;
    }

    .float-card--listing:hover {
      transform: translateY(-6px) rotate(-7deg);
    }

    .float-card-bottom {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: 10px;
      border-top: 2px dashed var(--gray-100);
    }

    .float-price {
      font-size: 17px;
      font-weight: 900;
      color: var(--green);
    }

    .float-price small {
      font-size: 11px;
      font-weight: 700;
      color: var(--text-muted);
    }

    .float-qr-icon {
      width: 30px;
      height: 30px;
      border-radius: 8px;
      background: var(--green-light);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
    }

    .float-card--score {
      top: 0;
      right: -6px;
      width: 184px;
      transform: rotate(5deg);
      z-index: 4;
      text-align: center;
    }

    .float-card--score:hover {
      transform: translateY(-6px) rotate(5deg);
    }

    .float-score-head {
      font-size: 11px;
      font-weight: 800;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
    }

    .float-score-num {
      font-size: 34px;
      font-weight: 900;
      color: var(--green);
      line-height: 1;
    }

    .float-score-num span {
      font-size: 15px;
      color: var(--text-muted);
      font-weight: 700;
    }

    .float-score-bar {
      height: 8px;
      background: var(--gray-100);
      border-radius: 4px;
      overflow: hidden;
      margin: 10px 0 8px;
    }

    .float-score-bar div {
      height: 100%;
      width: 92%;
      background: var(--green);
      border-radius: 4px;
    }

    .float-score-tag {
      font-size: 11px;
      font-weight: 800;
      color: var(--green-deeper);
      background: var(--green-light);
      border-radius: 100px;
      padding: 4px 10px;
      display: inline-block;
    }

    .float-card--qr {
      bottom: 54px;
      left: -16px;
      width: 172px;
      transform: rotate(-5deg);
      z-index: 2;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .float-card--qr:hover {
      transform: translateY(-6px) rotate(-5deg);
    }

    .float-qr-mini {
      width: 44px;
      height: 44px;
      border-radius: 8px;
      background: var(--white);
      border: 2px solid var(--gray-100);
      padding: 3px;
      flex-shrink: 0;
    }

    .float-qr-mini svg {
      width: 100%;
      height: 100%;
    }

    .float-card--signal {
      bottom: -6px;
      right: 20px;
      width: 230px;
      transform: rotate(6deg);
      z-index: 2;
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }

    .float-card--signal:hover {
      transform: translateY(-6px) rotate(6deg);
    }

    .float-signal-dot {
      width: 14px;
      height: 14px;
      border-radius: 50%;
      background: var(--green);
      flex-shrink: 0;
      margin-top: 3px;
      box-shadow: 0 0 0 4px var(--green-light);
    }

    /* ============ PROBLEM SECTION ============ */
    .problem {
      padding: 100px 0;
      background: var(--off-white);
    }

    .problem-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 80px;
      align-items: center;
    }

    .problem-cards {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .problem-card {
      background: var(--white);
      border-radius: var(--radius);
      padding: 24px;
      border: 2.5px solid var(--gray-100);
      box-shadow: 0 4px 0 var(--gray-100);
      display: flex;
      gap: 16px;
      align-items: flex-start;
    }

    .problem-icon {
      width: 48px;
      height: 48px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      flex-shrink: 0;
    }

    .problem-icon--red {
      background: #FFE0E0;
    }

    .problem-icon--orange {
      background: var(--yellow-light);
    }

    .problem-icon--gray {
      background: #EAEAEA;
    }

    .problem-card h3 {
      font-size: 16px;
      font-weight: 800;
      margin-bottom: 6px;
    }

    .problem-card p {
      font-size: 14px;
      color: var(--text-muted);
      line-height: 1.6;
    }

    .problem-arrow {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      padding: 20px 0;
    }

    .problem-arrow-line {
      width: 3px;
      height: 40px;
      background: linear-gradient(to bottom, var(--gray-200), var(--green));
      border-radius: 2px;
    }

    .solution-preview {
      background: var(--green-light);
      border: 2.5px solid var(--green);
      border-radius: var(--radius);
      padding: 24px;
      box-shadow: 0 4px 0 var(--green-dark);
    }

    .solution-preview h3 {
      font-size: 18px;
      font-weight: 800;
      color: var(--green-deeper);
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .solution-preview p {
      font-size: 14px;
      color: var(--green-deeper);
      line-height: 1.6;
    }

    /* ============ MODULES SECTION ============ */
    .modules {
      padding: 100px 0 60px;
    }

    .modules-header {
      text-align: center;
      max-width: 600px;
      margin: 0 auto 72px;
    }

    .modules-header .section-desc {
      margin: 0 auto;
    }

    /* Module 1: Marketplace */
    .module-marketplace {
      padding: 60px 0;
    }

    .module-marketplace-grid {
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      gap: 60px;
      align-items: center;
    }

    .marketplace-demo {
      background: var(--off-white);
      border-radius: var(--radius-lg);
      border: 2.5px solid var(--gray-100);
      overflow: hidden;
      box-shadow: 0 6px 0 var(--gray-100);
    }

    .marketplace-topbar {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 16px 20px;
      border-bottom: 2px solid var(--gray-100);
      background: var(--white);
    }

    .marketplace-topbar-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
    }

    .marketplace-search {
      flex: 1;
      background: var(--off-white);
      border-radius: 10px;
      padding: 8px 14px;
      font-size: 13px;
      color: var(--text-muted);
      font-weight: 600;
    }

    .marketplace-list {
      padding: 16px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .marketplace-item {
      display: flex;
      align-items: center;
      gap: 14px;
      background: var(--white);
      padding: 14px;
      border-radius: var(--radius);
      border: 2px solid var(--gray-100);
      transition: border-color 0.2s;
    }

    .marketplace-item:first-child {
      border-color: var(--green);
    }

    .marketplace-item-img {
      width: 52px;
      height: 52px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      flex-shrink: 0;
    }

    .marketplace-item-img--mango {
      background: #FFF3D6;
    }

    .marketplace-item-img--quyt {
      background: #FFE8D6;
    }

    .marketplace-item-img--nhan {
      background: #FFF0E0;
    }

    .marketplace-item-info {
      flex: 1;
    }

    .marketplace-item-name {
      font-size: 15px;
      font-weight: 800;
    }

    .marketplace-item-meta {
      font-size: 12px;
      color: var(--text-muted);
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 2px;
    }

    .marketplace-item-price {
      font-size: 16px;
      font-weight: 900;
      color: var(--green);
    }

    .marketplace-item-qr {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      background: var(--green-light);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
    }

    .module-features {
      display: flex;
      flex-direction: column;
      gap: 20px;
      margin-top: 32px;
    }

    .module-feature {
      display: flex;
      gap: 14px;
      align-items: flex-start;
    }

    .module-feature-icon {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: var(--green-light);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      flex-shrink: 0;
    }

    .module-feature h4 {
      font-size: 15px;
      font-weight: 800;
      margin-bottom: 4px;
    }

    .module-feature p {
      font-size: 14px;
      color: var(--text-muted);
      line-height: 1.5;
    }

    /* ============ LIVE CATALOG (real products from DB) ============ */
    .catalog {
      padding: 20px 0 100px;
    }

    .catalog-head {
      text-align: center;
      max-width: 600px;
      margin: 0 auto 36px;
    }

    .catalog-head .section-desc {
      margin: 0 auto;
    }

    .filter-bar {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      justify-content: center;
      margin-bottom: 32px;
    }

    .pill {
      font-family: 'Nunito', sans-serif;
      font-size: 14px;
      font-weight: 800;
      padding: 10px 20px;
      border-radius: 100px;
      background: var(--white);
      border: 2.5px solid var(--gray-200);
      color: var(--text-muted);
      box-shadow: 0 4px 0 var(--gray-200);
      cursor: pointer;
      transition: 0.15s;
    }

    .pill:active {
      transform: translateY(3px);
      box-shadow: 0 1px 0 var(--gray-200);
    }

    .pill.active {
      background: var(--green);
      border-color: var(--green-dark);
      color: var(--white);
      box-shadow: 0 4px 0 var(--green-dark);
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 20px;
    }

    .pcard {
      background: var(--white);
      border-radius: var(--radius-lg);
      border: 2.5px solid var(--gray-100);
      box-shadow: 0 5px 0 var(--gray-100);
      overflow: hidden;
      transition: transform 0.2s;
      display: flex;
      flex-direction: column;
    }

    .pcard:hover {
      transform: translateY(-3px);
    }

    .pcard.hidden {
      display: none;
    }

    .pcard-visual {
      aspect-ratio: 4/3;
      position: relative;
      background: var(--green-pale);
      overflow: hidden;
    }

    .pcard-visual img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .pc-badge {
      position: absolute;
      top: 10px;
      left: 10px;
      font-size: 11.5px;
      font-weight: 800;
      padding: 5px 10px;
      border-radius: 100px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .pc-badge.good {
      background: var(--green-light);
      color: var(--green-deeper);
    }

    .pc-badge.warn {
      background: var(--yellow-light);
      color: #996A00;
    }

    .pc-cert {
      position: absolute;
      top: 10px;
      right: 10px;
      font-size: 10.5px;
      font-weight: 800;
      padding: 4px 9px;
      border-radius: 100px;
      background: rgba(255, 255, 255, 0.92);
      color: var(--text);
    }

    .pcard-body {
      padding: 16px;
      display: flex;
      flex-direction: column;
      gap: 5px;
      flex: 1;
    }

    .pcard-name {
      font-weight: 800;
      font-size: 15px;
    }

    .pcard-farmer {
      font-size: 12px;
      color: var(--text-muted);
      font-weight: 600;
    }

    .pcard-bottom {
      margin-top: auto;
      padding-top: 12px;
      border-top: 2px dashed var(--gray-100);
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 12px;
    }

    .price {
      font-weight: 900;
      font-size: 17px;
      color: var(--green);
    }

    .price small {
      font-weight: 700;
      font-size: 11px;
      color: var(--text-muted);
    }

    .pbtns {
      display: flex;
      gap: 6px;
    }

    .btn-add {
      width: 34px;
      height: 34px;
      border-radius: 10px;
      background: var(--green);
      color: var(--white);
      border: none;
      font-size: 18px;
      font-weight: 800;
      cursor: pointer;
      box-shadow: 0 3px 0 var(--green-dark);
      transition: 0.15s;
    }

    .btn-add:active {
      transform: translateY(2px);
      box-shadow: 0 1px 0 var(--green-dark);
    }

    .btn-detail {
      padding: 0 12px;
      height: 34px;
      border-radius: 10px;
      font-size: 11px;
      font-weight: 800;
      background: var(--white);
      border: 2px solid var(--gray-200);
      color: var(--text-muted);
      cursor: pointer;
    }

    .btn-detail:hover {
      border-color: var(--green);
      color: var(--green-deeper);
    }

    /* Module 2: AI Quality - SIGNATURE SECTION */
    .module-ai {
      padding: 80px 0;
      background: var(--green-pale);
      position: relative;
      overflow: hidden;
    }

    .module-ai-grid {
      display: grid;
      grid-template-columns: 0.9fr 1.1fr;
      gap: 60px;
      align-items: center;
    }

    .ai-demo {
      position: relative;
      width: 100%;
      max-width: 440px;
    }

    .ai-phone {
      background: var(--white);
      border-radius: 32px;
      border: 3px solid var(--text);
      overflow: hidden;
      box-shadow: 0 8px 0 rgba(0, 0, 0, 0.08);
      position: relative;
      height: 730px;
      width: 360px;
    }

    .ai-phone-notch {
      width: 120px;
      height: 28px;
      background: var(--text);
      border-radius: 0 0 16px 16px;
      margin: 0 auto;
    }

    .ai-phone-screen {
      padding: 20px;
      min-height: 420px;
      display: flex;
      flex-direction: column;
    }

    .ai-phone-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
    }

    .ai-phone-header-back {
      font-size: 18px;
    }

    .ai-phone-header-title {
      font-size: 16px;
      font-weight: 800;
    }

    /* The scan target area */
    .ai-scan-area {
      position: relative;
      background: linear-gradient(135deg, #FFF8E1, #FFECB3);
      border-radius: var(--radius);
      height: 200px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 80px;
      overflow: hidden;
      margin-bottom: 16px;
    }

    .ai-scan-line {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, transparent, var(--green), transparent);
      box-shadow: 0 0 20px var(--green), 0 0 60px rgba(88, 204, 2, 0.3);
      opacity: 0;
      z-index: 2;
    }

    .ai-scan-corners {
      position: absolute;
      inset: 16px;
      border: 2.5px solid var(--green);
      border-radius: 8px;
      opacity: 0;
    }

    /* Result metrics that appear after scan */
    .ai-results {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .ai-result-row {
      display: flex;
      align-items: center;
      gap: 12px;
      opacity: 0;
      transform: translateY(10px);
    }

    .ai-result-label {
      font-size: 13px;
      font-weight: 700;
      color: var(--text-muted);
      width: 100px;
      flex-shrink: 0;
    }

    .ai-result-bar {
      flex: 1;
      height: 12px;
      background: var(--gray-100);
      border-radius: 6px;
      overflow: hidden;
      position: relative;
    }

    .ai-result-fill {
      height: 100%;
      border-radius: 6px;
      width: 0;
      transition: width 1s ease;
    }

    .ai-result-fill--green {
      background: var(--green);
    }

    .ai-result-fill--yellow {
      background: var(--yellow);
    }

    .ai-result-fill--orange {
      background: var(--orange);
    }

    .ai-result-value {
      font-size: 14px;
      font-weight: 900;
      width: 42px;
      text-align: right;
    }

    .ai-score-badge {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: var(--green);
      color: var(--white);
      padding: 12px;
      border-radius: var(--radius);
      font-weight: 800;
      font-size: 15px;
      margin-top: 8px;
      opacity: 0;
      transform: scale(0.9);
      box-shadow: 0 4px 0 var(--green-dark);
    }

    /* Module 3: Recommendation */
    .module-recommend {
      padding: 80px 0;
    }

    .module-recommend-grid {
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      gap: 60px;
      align-items: center;
    }

    .recommend-demo {
      background: var(--white);
      border-radius: var(--radius-lg);
      border: 2.5px solid var(--gray-100);
      box-shadow: 0 6px 0 var(--gray-100);
      overflow: hidden;
    }

    .recommend-header {
      padding: 20px 24px;
      border-bottom: 2px solid var(--gray-100);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .recommend-header h4 {
      font-size: 16px;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .recommend-date {
      font-size: 13px;
      color: var(--text-muted);
      font-weight: 600;
    }

    .recommend-body {
      padding: 24px;
    }

    .recommend-chart {
      display: flex;
      align-items: flex-end;
      gap: 8px;
      height: 120px;
      margin-bottom: 24px;
      padding-bottom: 4px;
      border-bottom: 2px solid var(--gray-100);
    }

    .recommend-bar {
      flex: 1;
      border-radius: 6px 6px 0 0;
      transition: height 0.8s ease;
      position: relative;
    }

    .recommend-bar::after {
      content: attr(data-label);
      position: absolute;
      bottom: -20px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 10px;
      font-weight: 700;
      color: var(--text-muted);
      white-space: nowrap;
    }

    .recommend-signal {
      margin-top: 32px;
      padding: 20px;
      border-radius: var(--radius);
      display: flex;
      gap: 14px;
      align-items: flex-start;
    }

    .recommend-signal--sell {
      background: var(--green-light);
      border: 2px solid var(--green);
    }

    .recommend-signal-icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      flex-shrink: 0;
    }

    .recommend-signal--sell .recommend-signal-icon {
      background: var(--green);
    }

    .recommend-signal h4 {
      font-size: 15px;
      font-weight: 800;
      color: var(--green-deeper);
      margin-bottom: 4px;
    }

    .recommend-signal p {
      font-size: 13px;
      color: var(--green-deeper);
      line-height: 1.5;
    }

    /* ============ AUDIENCE / HOW IT WORKS ============ */
    .audience {
      padding: 80px 0;
      background: var(--off-white);
    }

    .audience-header {
      text-align: center;
      max-width: 560px;
      margin: 0 auto 56px;
    }

    .audience-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      max-width: 840px;
      margin: 0 auto;
    }

    .audience-card {
      background: var(--white);
      border-radius: var(--radius-lg);
      padding: 36px 28px;
      border: 2.5px solid var(--gray-100);
      box-shadow: 0 5px 0 var(--gray-100);
      text-align: center;
      transition: transform 0.2s;
    }

    .audience-card:hover {
      transform: translateY(-4px);
    }

    .audience-card-icon {
      font-size: 52px;
      margin-bottom: 16px;
      line-height: 1;
    }

    .audience-card h3 {
      font-size: 20px;
      font-weight: 900;
      margin-bottom: 12px;
    }

    .audience-card p {
      font-size: 14px;
      color: var(--text-muted);
      line-height: 1.6;
    }

    .audience-card-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      justify-content: center;
      margin-top: 16px;
    }

    .audience-tag {
      padding: 6px 14px;
      border-radius: 100px;
      font-size: 12px;
      font-weight: 700;
    }

    .audience-tag--green {
      background: var(--green-light);
      color: var(--green-deeper);
    }

    .audience-tag--yellow {
      background: var(--yellow-light);
      color: #996A00;
    }

    /* ============ CTA ============ */
    .cta-section {
      padding: 100px 0;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .cta-bg {
      position: absolute;
      inset: 0;
      background: var(--green);
      border-radius: 40px;
      margin: 0 24px;
      z-index: 0;
    }

    .cta-content {
      position: relative;
      z-index: 1;
    }

    .cta-content .section-title {
      color: var(--white);
    }

    .cta-content .section-desc {
      color: rgba(255, 255, 255, 0.85);
      margin: 0 auto 36px;
      max-width: 480px;
    }

    .cta-btn {
      background: var(--white);
      color: var(--green-deeper);
      box-shadow: 0 5px 0 rgba(0, 0, 0, 0.15);
    }

    .cta-btn:active {
      box-shadow: 0 2px 0 rgba(0, 0, 0, 0.15);
    }

    .cta-emojis {
      display: flex;
      justify-content: center;
      gap: 16px;
      margin-top: 40px;
      font-size: 36px;
      opacity: 0.6;
    }

    /* ============ FOOTER ============ */
    .footer {
      padding: 48px 0 32px;
      border-top: 2px solid var(--gray-100);
    }

    .footer-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
    }

    .footer-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .footer-text {
      font-size: 14px;
      color: var(--text-muted);
      font-weight: 600;
    }

    .footer-text a {
      color: var(--green);
      text-decoration: none;
      font-weight: 700;
    }

    .footer-right {
      font-size: 13px;
      color: var(--text-muted);
    }

    /* ============ MODAL / TOAST (live catalog) ============ */
    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(60, 60, 60, 0.5);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 2000;
      padding: 20px;
    }

    .overlay.show {
      display: flex;
    }

    .modal {
      background: var(--white);
      border-radius: 24px;
      max-width: 440px;
      width: 100%;
      padding: 26px;
      position: relative;
      max-height: 88vh;
      overflow-y: auto;
      border: 2.5px solid var(--gray-100);
    }

    .modal-close {
      position: absolute;
      top: 16px;
      right: 16px;
      width: 32px;
      height: 32px;
      border-radius: 10px;
      background: var(--off-white);
      border: none;
      cursor: pointer;
      font-weight: 800;
      color: var(--text-muted);
    }

    .modal-title {
      font-weight: 900;
      font-size: 19px;
    }

    .modal-sub {
      font-size: 12px;
      color: var(--text-muted);
      font-weight: 700;
      margin-top: 2px;
    }

    .qr-row {
      display: flex;
      gap: 14px;
      align-items: center;
      margin: 16px 0;
      padding: 14px;
      background: var(--off-white);
      border-radius: 16px;
    }

    .qr-box {
      width: 48px;
      height: 48px;
      border-radius: 10px;
      background: var(--white);
      padding: 4px;
      flex-shrink: 0;
      border: 2px solid var(--gray-100);
    }

    .qr-box svg {
      width: 100%;
      height: 100%;
    }

    .qr-label {
      font-size: 12px;
      color: var(--text-muted);
      font-weight: 600;
    }

    .qr-label b {
      display: block;
      color: var(--text);
      font-size: 12.5px;
    }

    .ai-block {
      background: var(--green-pale);
      border-radius: 16px;
      padding: 16px;
      margin-bottom: 14px;
      border: 2px solid var(--green-light);
    }

    .ai-block-title {
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
      color: var(--green-deeper);
      margin-bottom: 10px;
    }

    .ai-row {
      display: flex;
      justify-content: space-between;
      font-size: 13px;
      padding: 6px 0;
      font-weight: 700;
      border-bottom: 1px dashed var(--gray-100);
    }

    .ai-row:last-child {
      border-bottom: none;
    }

    .ai-row b {
      color: var(--green-deeper);
    }

    .modal-reco {
      font-size: 13px;
      font-weight: 700;
      color: var(--green-deeper);
      background: var(--green-light);
      border-radius: 12px;
      padding: 12px 14px;
      margin-top: 8px;
      line-height: 1.5;
    }

    .modal-buy {
      width: 100%;
      height: 50px;
      border-radius: 14px;
      background: var(--green);
      color: var(--white);
      font-weight: 900;
      font-size: 14px;
      border: none;
      cursor: pointer;
      box-shadow: 0 5px 0 var(--green-dark);
      margin-top: 16px;
      transition: 0.15s;
    }

    .modal-buy:active {
      transform: translateY(3px);
      box-shadow: 0 2px 0 var(--green-dark);
    }

    .toast {
      position: fixed;
      bottom: 24px;
      left: 50%;
      transform: translateX(-50%) translateY(12px);
      background: var(--text);
      color: var(--white);
      font-weight: 800;
      font-size: 13px;
      padding: 12px 22px;
      border-radius: 100px;
      opacity: 0;
      transition: 0.3s;
      z-index: 3000;
      pointer-events: none;
    }

    .toast.show {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }

    /* ============ RESPONSIVE ============ */
    @media (max-width: 900px) {

      .hero-grid,
      .problem-grid,
      .module-marketplace-grid,
      .module-ai-grid,
      .module-recommend-grid {
        grid-template-columns: 1fr;
        gap: 40px;
      }

      .hero {
        min-height: auto;
        padding-top: 100px;
        padding-bottom: 60px;
      }

      .hero-visual {
        min-height: 340px;
      }

      .card-stack {
        width: 440px;
        height: 380px;
        transform: scale(0.78);
      }

      .hero-stats {
        gap: 24px;
      }

      .nav-links {
        display: none;
      }

      .nav-mobile-toggle {
        display: flex;
      }

      .module-ai-grid {
        direction: ltr;
      }

      .module-ai-grid> :first-child {
        order: 2;
      }

      .module-ai-grid> :last-child {
        order: 1;
      }

      .audience-grid {
        grid-template-columns: 1fr;
        max-width: 400px;
      }

      .cta-bg {
        border-radius: 24px;
        margin: 0 16px;
      }
    }

    @media (max-width: 480px) {
      .container {
        padding: 0 16px;
      }

      .hero-actions {
        flex-direction: column;
      }

      .btn-3d {
        width: 100%;
        justify-content: center;
      }

      .card-stack {
        transform: scale(0.62);
      }

      .hero-visual {
        min-height: 260px;
      }

      .hero-stats {
        flex-direction: column;
        gap: 12px;
      }
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {

      *,
      *::before,
      *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
      }
    }

    /* Scroll reveal base */
    .reveal {
      opacity: 0;
      transform: translateY(30px);
    }
  </style>
  @include('partials.pwa-head')
</head>

<body>

  <!-- ======= NAV ======= -->
  <nav class="nav" id="nav">
    <div class="container nav-inner">
      <a href="#" class="nav-logo">
        <div class="nav-logo-icon">🌱</div>
        <div class="nav-logo-text">Agri<span>SenAi</span></div>
      </a>
      <ul class="nav-links">
        <li><a href="#marketplace">Sàn kết nối</a></li>
        <li><a href="#products">Sản phẩm</a></li>
        <li><a href="#ai-quality">AI đánh giá</a></li>
        <li><a href="#recommend">Khuyến nghị</a></li>
        <li><a href="#cta" class="btn-3d btn-primary nav-cta">Bắt đầu</a></li>
      </ul>
      <button class="nav-mobile-toggle" id="navToggle" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>

  <!-- ======= HERO ======= -->
  <section class="hero" id="hero">
    <div class="container">
      <div class="hero-grid">
        <div class="hero-content">
          <div class="hero-badge reveal">
            <span class="hero-badge-dot"></span>
            Đồng Tháp · Khởi nghiệp xanh 2026
          </div>
          <h1 class="hero-title reveal">
            Nông sản đến tay<br>người mua,<br><span class="highlight">không qua thương lái.</span>
          </h1>
          <p class="hero-subtitle reveal">
            Sàn thương mại điện tử đầu tiên tại Đồng Tháp kết nối trực tiếp hộ nông dân, cơ sở OCOP với người mua — tích hợp AI đánh giá chất lượng nông sản qua ảnh chụp.
          </p>
          <div class="hero-actions reveal">
            <a href="#products" class="btn-3d btn-primary">Khám phá ngay</a>
            <a href="#ai-quality" class="btn-3d btn-secondary">Xem AI hoạt động</a>
          </div>
          <div class="hero-stats reveal">
            <div>
              <div class="hero-stat-num" data-count="3">0</div>
              <div class="hero-stat-label">Module tích hợp</div>
            </div>
            <div>
              <div class="hero-stat-num" data-count="5">0</div>
              <div class="hero-stat-label">Đặc sản Đồng Tháp</div>
            </div>
            <div>
              <div class="hero-stat-num">100%</div>
              <div class="hero-stat-label">Minh bạch nguồn gốc</div>
            </div>
          </div>
        </div>

        <div class="hero-visual reveal">
          <div class="card-stack" id="cardStack">
            <div class="float-blob"></div>

            <div class="float-card float-card--listing">
              <div class="float-card-top">
                <div class="float-emoji-box">🥭</div>
                <div>
                  <div class="float-card-title">Xoài cát Hòa Lộc</div>
                  <div class="float-card-meta">📍 Cao Lãnh, Đồng Tháp</div>
                </div>
              </div>
              <div class="float-card-bottom">
                <span class="float-price">45k<small>/kg</small></span>
                <span class="float-qr-icon">📱</span>
              </div>
            </div>

            <div class="float-card float-card--score">
              <div class="float-score-head">🤖 AI phân tích</div>
              <div class="float-score-num">9.2<span>/10</span></div>
              <div class="float-score-bar">
                <div></div>
              </div>
              <span class="float-score-tag">Loại A · Chín tới</span>
            </div>

            <div class="float-card float-card--qr">
              <div class="float-qr-mini">
                <svg viewBox="0 0 29 29">
                  <rect width="29" height="29" fill="white" />
                  <g fill="#3C3C3C">
                    <rect x="2" y="2" width="7" height="7" />
                    <rect x="20" y="2" width="7" height="7" />
                    <rect x="2" y="20" width="7" height="7" />
                    <rect x="4" y="4" width="3" height="3" fill="white" />
                    <rect x="22" y="4" width="3" height="3" fill="white" />
                    <rect x="4" y="22" width="3" height="3" fill="white" />
                    <rect x="11" y="2" width="2" height="2" />
                    <rect x="14" y="4" width="2" height="2" />
                    <rect x="11" y="7" width="2" height="2" />
                    <rect x="17" y="11" width="2" height="2" />
                    <rect x="20" y="13" width="2" height="2" />
                    <rect x="11" y="14" width="2" height="2" />
                    <rect x="14" y="17" width="2" height="2" />
                    <rect x="17" y="20" width="2" height="2" />
                    <rect x="20" y="17" width="2" height="2" />
                    <rect x="11" y="20" width="2" height="2" />
                    <rect x="14" y="23" width="2" height="2" />
                    <rect x="23" y="23" width="2" height="2" />
                  </g>
                </svg>
              </div>
              <div class="float-card-meta">Quét mã QR<br><b style="color:var(--text)">Truy xuất nguồn gốc</b></div>
            </div>

            <div class="float-card float-card--signal">
              <div class="float-signal-dot"></div>
              <div>
                <div class="float-card-title" style="color:var(--green-deeper)">Nên bán ngay</div>
                <div class="float-card-meta">Giá xoài đang cao nhất tuần</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ======= PROBLEM ======= -->
  <section class="problem" id="problem">
    <div class="container">
      <div class="problem-grid">
        <div>
          <span class="eyebrow reveal">Vấn đề</span>
          <h2 class="section-title reveal">Nông dân đang mất quyền định giá nông sản.</h2>
          <p class="section-desc reveal">
            Phụ thuộc thương lái, thiếu công cụ đánh giá khách quan, không biết khi nào nên bán — đó là thực trạng mà hàng nghìn hộ nông dân Đồng Tháp đang đối mặt mỗi mùa vụ.
          </p>
        </div>
        <div>
          <div class="problem-cards">
            <div class="problem-card reveal">
              <div class="problem-icon problem-icon--red">📉</div>
              <div>
                <h3>Bị ép giá qua thương lái</h3>
                <p>Nông dân phụ thuộc trung gian, không có kênh tiếp cận người mua trực tiếp. Giá bán thường thấp hơn 20–40% so với giá thị trường.</p>
              </div>
            </div>
            <div class="problem-card reveal">
              <div class="problem-icon problem-icon--orange">🔍</div>
              <div>
                <h3>Không có cách đánh giá chất lượng</h3>
                <p>Chất lượng nông sản được đánh giá cảm tính, gây tranh cãi giá cả và giảm lòng tin người mua.</p>
              </div>
            </div>
            <div class="problem-card reveal">
              <div class="problem-icon problem-icon--gray">⏰</div>
              <div>
                <h3>Bán sai thời điểm</h3>
                <p>Thiếu thông tin thời tiết và biến động giá thị trường để quyết định thời điểm bán tối ưu.</p>
              </div>
            </div>
          </div>

          <div class="problem-arrow reveal">
            <div class="problem-arrow-line"></div>
            <span style="font-size: 20px;">⬇️</span>
          </div>

          <div class="solution-preview reveal">
            <h3>🌱 AgriSenAi giải quyết cả 3</h3>
            <p>Một nền tảng duy nhất: kết nối trực tiếp → AI đánh giá khách quan → khuyến nghị thời điểm bán. Ba module liên kết chặt trong một luồng.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ======= MODULES ======= -->
  <section class="modules" id="modules">
    <div class="container">
      <div class="modules-header">
        <span class="eyebrow reveal">3 Module cốt lõi</span>
        <h2 class="section-title reveal">Một luồng liền mạch,<br>từ vườn đến người mua.</h2>
        <p class="section-desc reveal">Không phải 3 công cụ rời rạc — kết quả AI được dùng ngay để đăng bán và sinh khuyến nghị.</p>
      </div>
    </div>
  </section>

  <!-- Module 1: Marketplace -->
  <section class="module-marketplace" id="marketplace">
    <div class="container">
      <div class="module-marketplace-grid">
        <div class="marketplace-demo reveal">
          <div class="marketplace-topbar">
            <div class="marketplace-topbar-dot" style="background: var(--green)"></div>
            <div class="marketplace-search">🔍 Tìm nông sản Đồng Tháp...</div>
          </div>
          <div class="marketplace-list">
            <div class="marketplace-item">
              <div class="marketplace-item-img marketplace-item-img--mango">🥭</div>
              <div class="marketplace-item-info">
                <div class="marketplace-item-name">Xoài cát Hòa Lộc</div>
                <div class="marketplace-item-meta">
                  <span>📍 Cao Lãnh</span>
                  <span>·</span>
                  <span>⭐ 9.2 AI Score</span>
                </div>
              </div>
              <div class="marketplace-item-price">45k/kg</div>
              <div class="marketplace-item-qr">📱</div>
            </div>
            <div class="marketplace-item">
              <div class="marketplace-item-img marketplace-item-img--quyt">🍊</div>
              <div class="marketplace-item-info">
                <div class="marketplace-item-name">Quýt hồng Lai Vung</div>
                <div class="marketplace-item-meta">
                  <span>📍 Lai Vung</span>
                  <span>·</span>
                  <span>⭐ 8.8 AI Score</span>
                </div>
              </div>
              <div class="marketplace-item-price">35k/kg</div>
              <div class="marketplace-item-qr">📱</div>
            </div>
            <div class="marketplace-item">
              <div class="marketplace-item-img marketplace-item-img--nhan">🫐</div>
              <div class="marketplace-item-info">
                <div class="marketplace-item-name">Nhãn xuồng Châu Thành</div>
                <div class="marketplace-item-meta">
                  <span>📍 Châu Thành</span>
                  <span>·</span>
                  <span>⭐ 9.0 AI Score</span>
                </div>
              </div>
              <div class="marketplace-item-price">55k/kg</div>
              <div class="marketplace-item-qr">📱</div>
            </div>
          </div>
        </div>

        <div>
          <span class="eyebrow reveal">Module 1</span>
          <h2 class="section-title reveal">Sàn kết nối giữa <br> nông dân — người mua.</h2>
          <p class="section-desc reveal">Đăng bán trực tiếp, không qua trung gian. Mỗi sản phẩm đều có mã QR truy xuất nguồn gốc — từ vườn nào, ai trồng, AI chấm bao nhiêu điểm.</p>
          <div class="module-features reveal">
            <div class="module-feature">
              <div class="module-feature-icon">🔗</div>
              <div>
                <h4>Kết nối trực tiếp</h4>
                <p>Nông dân tự đăng bán, người mua đặt hàng — bỏ trung gian, tăng thu nhập.</p>
              </div>
            </div>
            <div class="module-feature">
              <div class="module-feature-icon">📱</div>
              <div>
                <h4>QR truy xuất nguồn gốc</h4>
                <p>Quét mã biết ngay: ai trồng, ở đâu, chất lượng AI đánh giá ra sao.</p>
              </div>
            </div>
            <div class="module-feature">
              <div class="module-feature-icon">🏷️</div>
              <div>
                <h4>Lọc theo OCOP, vùng trồng</h4>
                <p>Tìm nông sản theo loại, chứng nhận OCOP, hoặc vùng sản xuất cụ thể.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Live catalog — real products from HomeController -->
  <section class="catalog" id="products">
    <div class="container">
      <div class="catalog-head">
        <span class="eyebrow reveal">Đang mở bán</span>
        <h2 class="section-title reveal">Toàn bộ sản phẩm trên sàn</h2>
        <p class="section-desc reveal">Mỗi sản phẩm đều có điểm AI và trang truy xuất nguồn gốc riêng.</p>
      </div>
      <div class="filter-bar reveal" id="filterBar">
        <button class="pill active" data-cat="all">Tất cả</button>
        @php $categoryLabels = ['trai-cay' => 'Trái cây', 'dac-san' => 'Đặc sản']; @endphp
        @foreach($categories as $cat)
        <button class="pill" data-cat="{{ $cat }}">{{ $categoryLabels[$cat] ?? ucfirst($cat) }}</button>
        @endforeach
      </div>
      <div class="grid reveal" id="grid"></div>
    </div>
  </section>

  <!-- Module 2: AI Quality Assessment - SIGNATURE -->
  <section class="module-ai" id="ai-quality">
    <div class="container">
      <div class="module-ai-grid">
        <div>
          <span class="eyebrow reveal">Module 2 · ⭐ Tính năng cốt lõi</span>
          <h2 class="section-title reveal">Chụp ảnh.<br>AI chấm điểm.</h2>
          <p class="section-desc reveal">
            Gemini Vision phân tích ảnh chụp nông sản: đánh giá độ chín, phát hiện hư hỏng, chấm điểm chất lượng tổng hợp. Kết quả khách quan, không tranh cãi.
          </p>
          <div class="module-features reveal">
            <div class="module-feature">
              <div class="module-feature-icon">📸</div>
              <div>
                <h4>Chụp ảnh tại vườn</h4>
                <p>Dùng điện thoại chụp trực tiếp — AI xử lý ngay trên ứng dụng.</p>
              </div>
            </div>
            <div class="module-feature">
              <div class="module-feature-icon">🧠</div>
              <div>
                <h4>Phân tích đa tiêu chí</h4>
                <p>Độ chín, màu sắc, kích thước, hư hỏng — tất cả trong một lần chụp.</p>
              </div>
            </div>
            <div class="module-feature">
              <div class="module-feature-icon">✅</div>
              <div>
                <h4>Điểm chất lượng minh bạch</h4>
                <p>Điểm AI gắn liền sản phẩm khi đăng bán — người mua tin tưởng hơn.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="ai-demo reveal" id="aiDemo">
          <div class="ai-phone">
            <div class="ai-phone-notch"></div>
            <div class="ai-phone-screen">
              <div class="ai-phone-header">
                <span class="ai-phone-header-back">←</span>
                <span class="ai-phone-header-title">AI Đánh giá chất lượng</span>
              </div>

              <div class="ai-scan-area" id="aiScanArea" style="background-image:url('{{ asset('assets/images/mon-ngon-dong-thap-xoai-cat-hoa-loc.jpeg') }}'); background-size:cover; background-position:center;">
                <div class="ai-scan-corners" id="aiCorners"></div>
                <div class="ai-scan-line" id="aiScanLine"></div>
              </div>

              <div class="ai-results" id="aiResults">
                <div class="ai-result-row">
                  <span class="ai-result-label">Độ chín</span>
                  <div class="ai-result-bar">
                    <div class="ai-result-fill ai-result-fill--green" data-width="88%"></div>
                  </div>
                  <span class="ai-result-value">88%</span>
                </div>
                <div class="ai-result-row">
                  <span class="ai-result-label">Màu sắc</span>
                  <div class="ai-result-bar">
                    <div class="ai-result-fill ai-result-fill--green" data-width="92%"></div>
                  </div>
                  <span class="ai-result-value">92%</span>
                </div>
                <div class="ai-result-row">
                  <span class="ai-result-label">Kích thước</span>
                  <div class="ai-result-bar">
                    <div class="ai-result-fill ai-result-fill--yellow" data-width="75%"></div>
                  </div>
                  <span class="ai-result-value">75%</span>
                </div>
                <div class="ai-result-row">
                  <span class="ai-result-label">Hư hỏng</span>
                  <div class="ai-result-bar">
                    <div class="ai-result-fill ai-result-fill--green" data-width="5%"></div>
                  </div>
                  <span class="ai-result-value">5%</span>
                </div>
                <div class="ai-score-badge" id="aiScoreBadge">
                  🏆 Điểm chất lượng: 9.2 / 10 — Loại A
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Module 3: Sell Recommendation -->
  <section class="module-recommend" id="recommend">
    <div class="container">
      <div class="module-recommend-grid">
        <div class="recommend-demo reveal" id="recommendDemo">
          <div class="recommend-header">
            <h4>📊 Khuyến nghị bán hàng</h4>
            <span class="recommend-date">Tháng 7, 2026</span>
          </div>
          <div class="recommend-body">
            <p style="font-size: 13px; font-weight: 700; color: var(--text-muted); margin-bottom: 12px;">Giá xoài Cao Lãnh (nghìn VNĐ/kg)</p>
            <div class="recommend-chart" id="recommendChart">
              <div class="recommend-bar" data-label="T2" data-height="35%" style="height: 0; background: var(--gray-200);"></div>
              <div class="recommend-bar" data-label="T3" data-height="45%" style="height: 0; background: var(--gray-200);"></div>
              <div class="recommend-bar" data-label="T4" data-height="55%" style="height: 0; background: var(--yellow);"></div>
              <div class="recommend-bar" data-label="T5" data-height="70%" style="height: 0; background: var(--yellow);"></div>
              <div class="recommend-bar" data-label="T6" data-height="85%" style="height: 0; background: var(--orange);"></div>
              <div class="recommend-bar" data-label="T7" data-height="95%" style="height: 0; background: var(--green);"></div>
              <div class="recommend-bar" data-label="T8" data-height="60%" style="height: 0; background: var(--yellow);"></div>
              <div class="recommend-bar" data-label="T9" data-height="40%" style="height: 0; background: var(--gray-200);"></div>
            </div>

            <div class="recommend-signal recommend-signal--sell">
              <div class="recommend-signal-icon">🟢</div>
              <div>
                <h4>Nên bán ngay — giá đang cao nhất</h4>
                <p>Giá xoài cát Hòa Lộc đang ở mức 45.000đ/kg (cao nhất 6 tháng). Dự báo thời tiết tuần tới có mưa — giá có thể giảm. AI khuyến nghị: <strong>bán trong 3–5 ngày tới.</strong></p>
              </div>
            </div>
          </div>
        </div>

        <div>
          <span class="eyebrow reveal">Module 3</span>
          <h2 class="section-title reveal">Biết lúc nào<br>nên bán.</h2>
          <p class="section-desc reveal">
            Kết hợp dữ liệu thời tiết thực tế và giá thị trường — Gemini AI tổng hợp khuyến nghị bằng ngôn ngữ tự nhiên, dễ hiểu cho nông dân.
          </p>
          <div class="module-features reveal">
            <div class="module-feature">
              <div class="module-feature-icon">🌤️</div>
              <div>
                <h4>Dữ liệu thời tiết thực</h4>
                <p>Tích hợp API thời tiết — mưa bão, nắng hạn đều ảnh hưởng giá.</p>
              </div>
            </div>
            <div class="module-feature">
              <div class="module-feature-icon">💰</div>
              <div>
                <h4>Theo dõi giá thị trường</h4>
                <p>Cập nhật giá tham khảo theo ngày, so sánh giữa các vùng.</p>
              </div>
            </div>
            <div class="module-feature">
              <div class="module-feature-icon">💬</div>
              <div>
                <h4>Khuyến nghị bằng tiếng Việt</h4>
                <p>"Nên bán ngay" hay "chờ thêm 3 ngày" — rõ ràng, không cần biết kỹ thuật.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ======= AUDIENCE ======= -->
  <section class="audience" id="audience">
    <div class="container">
      <div class="audience-header">
        <span class="eyebrow reveal">Dành cho ai?</span>
        <h2 class="section-title reveal">Hai phía, một nền tảng.</h2>
      </div>
      <div class="audience-grid">
        <div class="audience-card reveal">
          <div class="audience-card-icon">👨‍🌾</div>
          <h3>Nông dân & Hộ OCOP</h3>
          <p>Đăng bán nông sản trực tiếp, để AI đánh giá chất lượng khách quan, nhận khuyến nghị thời điểm bán tối ưu — tăng giá trị sản phẩm, giảm phụ thuộc thương lái.</p>
          <div class="audience-card-tags">
            <span class="audience-tag audience-tag--green">Tự đăng bán</span>
            <span class="audience-tag audience-tag--green">AI chấm điểm</span>
            <span class="audience-tag audience-tag--yellow">Tư vấn bán</span>
          </div>
        </div>
        <div class="audience-card reveal">
          <div class="audience-card-icon">🛒</div>
          <h3>Người mua & Thu mua</h3>
          <p>Tìm nguồn nông sản chất lượng, minh bạch nguồn gốc qua QR. Người mua lẻ, tiểu thương, đơn vị thu mua — đều có thể đặt hàng trực tiếp từ vườn.</p>
          <div class="audience-card-tags">
            <span class="audience-tag audience-tag--green">Truy xuất QR</span>
            <span class="audience-tag audience-tag--green">Mua trực tiếp</span>
            <span class="audience-tag audience-tag--yellow">Giá gốc</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ======= CTA ======= -->
  <section class="cta-section" id="cta">
    <div class="container" style="position: relative;">
      <div class="cta-bg reveal"></div>
      <div class="cta-content">
        <h2 class="section-title reveal">Sẵn sàng đưa nông sản lên sàn?</h2>
        <p class="section-desc reveal">AgriSenAi — nơi nông sản Đồng Tháp được định giá đúng giá trị thực, bằng công nghệ AI.</p>
        <a href="#" class="btn-3d cta-btn reveal">Đăng ký trải nghiệm</a>
        <div class="cta-emojis reveal">
          <span>🥭</span><span>🍊</span><span>🌿</span><span>🍈</span><span>🌾</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ======= FOOTER ======= -->
  <footer class="footer">
    <div class="container footer-inner">
      <div class="footer-left">
        <div class="nav-logo-icon" style="width: 32px; height: 32px; font-size: 16px; border-radius: 10px; box-shadow: 0 2px 0 var(--green-dark);">🌱</div>
        <span class="footer-text"><strong>AgriSenAi</strong> · Dự án dự thi <a href="#">Khởi nghiệp Đồng Tháp 2026</a></span>
      </div>
      <div class="footer-right">Cao Phúc Thịnh · ĐH Tiền Giang</div>
    </div>
  </footer>

  <div class="overlay" id="overlay">
    <div class="modal" id="modalContent"></div>
  </div>
  <div class="toast" id="toast">Đã thêm vào giỏ</div>

  <!-- ======= SCRIPTS ======= -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
  <script>
    gsap.registerPlugin(ScrollTrigger);

    // ---- Nav scroll effect ----
    window.addEventListener('scroll', () => {
      document.getElementById('nav').classList.toggle('scrolled', window.scrollY > 20);
    });

    // ---- Mobile nav toggle (simple) ----
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.querySelector('.nav-links');
    navToggle.addEventListener('click', () => {
      const isOpen = navLinks.style.display === 'flex';
      navLinks.style.display = isOpen ? 'none' : 'flex';
      if (!isOpen) {
        Object.assign(navLinks.style, {
          position: 'absolute',
          top: '68px',
          left: '0',
          right: '0',
          background: 'white',
          flexDirection: 'column',
          padding: '20px 24px',
          gap: '16px',
          borderBottom: '2px solid var(--gray-100)',
          boxShadow: '0 8px 24px rgba(0,0,0,0.08)'
        });
      }
    });

    // ---- Scroll reveals ----
    document.querySelectorAll('.reveal').forEach((el, i) => {
      gsap.fromTo(el, {
        opacity: 0,
        y: 30
      }, {
        opacity: 1,
        y: 0,
        duration: 0.7,
        ease: 'power2.out',
        scrollTrigger: {
          trigger: el,
          start: 'top 88%',
          toggleActions: 'play none none none'
        },
        delay: (i % 4) * 0.08
      });
    });

    // ---- Hero floating UI cards animation ----
    gsap.to('.float-card--listing', {
      y: -10,
      rotation: -10,
      duration: 3,
      ease: 'sine.inOut',
      yoyo: true,
      repeat: -1
    });
    gsap.to('.float-card--score', {
      y: 8,
      rotation: 8,
      duration: 2.6,
      ease: 'sine.inOut',
      yoyo: true,
      repeat: -1,
      delay: 0.4
    });
    gsap.to('.float-card--qr', {
      y: -7,
      rotation: -8,
      duration: 3.4,
      ease: 'sine.inOut',
      yoyo: true,
      repeat: -1,
      delay: 0.2
    });
    gsap.to('.float-card--signal', {
      y: 9,
      rotation: 9,
      duration: 3.1,
      ease: 'sine.inOut',
      yoyo: true,
      repeat: -1,
      delay: 0.6
    });

    // ---- AI SCAN ANIMATION (Signature) ----
    const aiTl = gsap.timeline({
      scrollTrigger: {
        trigger: '#aiDemo',
        start: 'top 70%',
        toggleActions: 'play none none none'
      }
    });

    aiTl
      .to('#aiCorners', {
        opacity: 1,
        duration: 0.3
      })
      .to('#aiScanLine', {
        opacity: 1,
        duration: 0.2
      })
      .to('#aiScanLine', {
        top: '100%',
        duration: 1.5,
        ease: 'power1.inOut'
      })
      .to('#aiScanLine', {
        opacity: 0,
        duration: 0.2
      })
      .to('#aiCorners', {
        opacity: 0,
        duration: 0.3
      }, '-=0.2');

    // Show results after scan
    document.querySelectorAll('.ai-result-row').forEach((row, i) => {
      aiTl.to(row, {
        opacity: 1,
        y: 0,
        duration: 0.3,
        ease: 'power2.out'
      }, `-=${i > 0 ? 0.15 : 0}`);
      const fill = row.querySelector('.ai-result-fill');
      if (fill) {
        aiTl.to(fill, {
          width: fill.dataset.width,
          duration: 0.6,
          ease: 'power2.out'
        }, '<');
      }
    });

    aiTl.to('#aiScoreBadge', {
      opacity: 1,
      scale: 1,
      duration: 0.4,
      ease: 'back.out(2)'
    });

    // ---- CHART BARS ANIMATION ----
    const bars = document.querySelectorAll('.recommend-bar');
    bars.forEach((bar) => {
      gsap.to(bar, {
        height: bar.dataset.height,
        duration: 0.8,
        ease: 'power2.out',
        scrollTrigger: {
          trigger: '#recommendDemo',
          start: 'top 75%',
          toggleActions: 'play none none none'
        },
        delay: Array.from(bars).indexOf(bar) * 0.08
      });
    });

    // ---- Stat counter ----
    document.querySelectorAll('[data-count]').forEach(el => {
      const target = parseInt(el.dataset.count);
      gsap.to(el, {
        innerHTML: target,
        duration: 1.5,
        ease: 'power2.out',
        snap: {
          innerHTML: 1
        },
        scrollTrigger: {
          trigger: el,
          start: 'top 90%'
        }
      });
    });

    // ---- LIVE CATALOG: real products from HomeController ----
    const QR_SVG = `<svg viewBox="0 0 29 29"><rect width="29" height="29" fill="white"/><g fill="#3C3C3C">
<rect x="2" y="2" width="7" height="7"/><rect x="20" y="2" width="7" height="7"/><rect x="2" y="20" width="7" height="7"/>
<rect x="4" y="4" width="3" height="3" fill="white"/><rect x="22" y="4" width="3" height="3" fill="white"/><rect x="4" y="22" width="3" height="3" fill="white"/>
<rect x="11" y="2" width="2" height="2"/><rect x="14" y="4" width="2" height="2"/><rect x="11" y="7" width="2" height="2"/>
<rect x="17" y="11" width="2" height="2"/><rect x="20" y="13" width="2" height="2"/><rect x="11" y="14" width="2" height="2"/>
<rect x="14" y="17" width="2" height="2"/><rect x="17" y="20" width="2" height="2"/><rect x="20" y="17" width="2" height="2"/>
<rect x="11" y="20" width="2" height="2"/><rect x="14" y="23" width="2" height="2"/><rect x="23" y="23" width="2" height="2"/>
</g></svg>`;

    const PRODUCTS = @json($products);
    const grid = document.getElementById('grid');

    function renderProducts() {
      grid.innerHTML = PRODUCTS.map(p => `
    <div class="pcard" data-cat="${p.cat}">
      <div class="pcard-visual">
        <div class="pc-badge ${p.ai.good ? 'good' : 'warn'}">${p.ai.good ? '✓' : '!'} ${p.ai.ripeness}</div>
        <div class="pc-cert">${p.ocop}</div>
        <img src="https://picsum.photos/seed/${p.id}/400/300" alt="${p.name}" loading="lazy">
      </div>
      <div class="pcard-body">
        <div class="pcard-name">${p.name}</div>
        <div class="pcard-farmer">${p.farmer} · ${p.region}</div>
        <div class="pcard-bottom">
          <div class="price">${p.price}đ<small> /${p.unit}</small></div>
          <div class="pbtns">
            <button class="btn-detail" onclick="openModal(${p.id})">Chi tiết</button>
            <button class="btn-add" onclick="addToCart(${p.id})">+</button>
          </div>
        </div>
      </div>
    </div>
  `).join('');
    }
    renderProducts();

    document.getElementById('filterBar').addEventListener('click', (e) => {
      const btn = e.target.closest('.pill');
      if (!btn) return;
      document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      const cat = btn.dataset.cat;
      document.querySelectorAll('.pcard').forEach(card => {
        card.classList.toggle('hidden', cat !== 'all' && card.dataset.cat !== cat);
      });
    });

    let cartCount = 0;

    function addToCart(id) {
      cartCount++;
      showToast('Đã thêm vào giỏ');
    }

    function showToast(msg) {
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 1800);
    }

    function openModal(id) {
      const p = PRODUCTS.find(x => x.id === id);
      const modal = document.getElementById('modalContent');
      modal.innerHTML = `
    <div class="modal-close" onclick="closeModal()">✕</div>
    <div style="margin-bottom:16px;">
      <div class="modal-title">${p.name}</div>
      <div class="modal-sub">${p.farmer} · ${p.region} · ${p.ocop}</div>
    </div>
    <div class="qr-row">
      <div class="qr-box">${QR_SVG}</div>
      <div class="qr-label">Quét mã QR để xem hành trình<br><b>THU HOẠCH → ĐÓNG GÓI → ĐĂNG BÁN</b></div>
    </div>
    <div class="ai-block">
      <div class="ai-block-title">🤖 Kết quả đánh giá AI</div>
      <div class="ai-row"><span>Tình trạng</span><b>${p.ai.ripeness}</b></div>
      <div class="ai-row"><span>Điểm chất lượng</span><b>${p.ai.score}/100</b></div>
      <div class="ai-row"><span>Lỗi phát hiện</span><b>${p.ai.defect}</b></div>
      <div class="modal-reco">💬 "${p.ai.reco}"</div>
    </div>
    <button class="modal-buy" onclick="addToCart(${p.id}); closeModal();">Liên hệ ngay — ${p.price}đ/${p.unit}</button>
  `;
      document.getElementById('overlay').classList.add('show');
    }

    function closeModal() {
      document.getElementById('overlay').classList.remove('show');
    }
    document.getElementById('overlay').addEventListener('click', (e) => {
      if (e.target.id === 'overlay') closeModal();
    });
  </script>
  @include('partials.pwa-register')
</body>

</html>