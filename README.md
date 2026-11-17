<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#080b18">

  <title>Universal Agent OS (UAOS) | Astrova</title>

  <meta name="description" content="Universal Agent OS — منظومة هندسية متخصصة في مشاريع Laravel تضم 112 وكيلاً هندسياً موزعين على 14 قسماً، وتجمع الذكاء الاصطناعي والبحث والحوكمة وضمان الجودة.">

  <meta property="og:title" content="Universal Agent OS | Astrova">
  <meta property="og:description" content="منظومة تشغيل هندسية متخصصة في تطوير مشاريع Laravel.">
  <meta property="og:type" content="website">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Tajawal:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

  <style>
    :root {
      --bg: #080b18;
      --bg-soft: #0d1227;
      --panel: #111832;
      --panel-light: #17213d;
      --text: #f4f6ff;
      --muted: #a5aecb;
      --muted-2: #7885a8;
      --line: rgba(163, 177, 255, .14);
      --purple: #9b7cff;
      --blue: #56a8ff;
      --cyan: #65dedb;
      --green: #67d9a7;
      --max: 1180px;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      scroll-behavior: smooth;
      scroll-padding-top: 90px;
    }

    body {
      font-family: "Cairo", "Tajawal", sans-serif;
      background:
        radial-gradient(ellipse at 10% 5%, rgba(120, 78, 230, .16), transparent 35rem),
        radial-gradient(ellipse at 95% 25%, rgba(42, 142, 225, .12), transparent 35rem),
        var(--bg);
      color: var(--text);
      line-height: 1.9;
      overflow-x: hidden;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    button {
      font-family: inherit;
    }

    .container {
      width: min(var(--max), calc(100% - 40px));
      margin-inline: auto;
    }

    .section {
      padding: 90px 0;
    }

    .section-head {
      max-width: 790px;
      margin-bottom: 38px;
    }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      color: #c6b8ff;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .08em;
      padding: 7px 14px;
      border: 1px solid rgba(155, 124, 255, .25);
      border-radius: 50px;
      background: rgba(155, 124, 255, .07);
    }

    .eyebrow::before {
      content: "";
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--cyan);
      box-shadow: 0 0 14px var(--cyan);
    }

    .section-label {
      color: var(--purple);
      font-size: 11px;
      font-weight: 900;
      letter-spacing: .16em;
      margin-bottom: 8px;
    }

    h2 {
      font-size: clamp(27px, 4vw, 42px);
      line-height: 1.4;
      margin-bottom: 14px;
    }

    .section-head p {
      color: var(--muted);
      font-size: 14px;
    }

    .gradient {
      background: linear-gradient(100deg, #c2aaff, #69c6ff 65%, #70e3d9);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    .topbar {
      position: sticky;
      top: 0;
      z-index: 100;
      background: rgba(8, 11, 24, .88);
      backdrop-filter: blur(18px);
      border-bottom: 1px solid var(--line);
    }

    .nav {
      min-height: 76px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 11px;
      flex-shrink: 0;
    }

    .brand-mark {
      width: 43px;
      height: 43px;
      display: grid;
      place-items: center;
      border-radius: 14px;
      font-size: 21px;
      font-weight: 900;
      background: linear-gradient(135deg, var(--purple), var(--blue));
      box-shadow: 0 8px 28px rgba(120, 100, 255, .3);
    }

    .brand-name {
      font-weight: 900;
      letter-spacing: .08em;
      font-size: 15px;
      line-height: 1.4;
    }

    .brand-name small {
      display: block;
      font-size: 9px;
      font-weight: 600;
      color: var(--muted-2);
      letter-spacing: .15em;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 19px;
      font-size: 11px;
      color: var(--muted);
    }

    .nav-links a {
      transition: color .2s;
    }

    .nav-links a:hover {
      color: white;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 9px;
      padding: 12px 20px;
      border-radius: 12px;
      font-size: 13px;
      font-weight: 800;
      background: linear-gradient(110deg, #8d6cf4, #388fe9);
      color: white;
      box-shadow: 0 10px 30px rgba(88, 111, 255, .2);
      transition: transform .2s, box-shadow .2s;
    }

    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 14px 35px rgba(88, 111, 255, .35);
    }

    .btn-outline {
      background: rgba(255,255,255,.035);
      border: 1px solid var(--line);
      box-shadow: none;
    }

    .hero {
      padding: 105px 0 90px;
    }

    .hero-grid {
      display: grid;
      grid-template-columns: 1.1fr .9fr;
      gap: 55px;
      align-items: center;
    }

    .hero h1 {
      font-size: clamp(40px, 6vw, 70px);
      line-height: 1.25;
      margin: 23px 0 18px;
      font-weight: 900;
    }

    .hero-copy {
      color: var(--muted);
      font-size: 16px;
      max-width: 690px;
    }

    .hero-actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 30px;
    }

    .hero-note {
      color: var(--muted-2);
      font-size: 11px;
      margin-top: 20px;
    }

    .hero-visual {
      position: relative;
      padding: 27px;
      min-height: 440px;
      border: 1px solid var(--line);
      border-radius: 28px;
      overflow: hidden;
      background:
        radial-gradient(circle at 50% 45%, rgba(117, 95, 255, .18), transparent 15rem),
        linear-gradient(145deg, #171e40, #0c1229);
      box-shadow: 0 30px 90px rgba(0,0,0,.28);
    }

    .visual-title {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: .1em;
    }

    .visual-status {
      color: var(--cyan);
      font-size: 10px;
    }

    .orbit-area {
      height: 315px;
      display: grid;
      place-items: center;
      position: relative;
    }

    .orbit {
      position: absolute;
      border: 1px solid rgba(163, 177, 255, .2);
      border-radius: 50%;
    }

    .orbit-a {
      width: 210px;
      height: 210px;
    }

    .orbit-b {
      width: 280px;
      height: 280px;
      border-style: dashed;
      animation: rotate 35s linear infinite;
    }

    .orbit-c {
      width: 160px;
      height: 160px;
      border-color: rgba(101, 222, 219, .2);
    }

    @keyframes rotate {
      to { transform: rotate(360deg); }
    }

    .core {
      width: 115px;
      height: 115px;
      display: grid;
      place-items: center;
      text-align: center;
      border-radius: 31px;
      background: linear-gradient(145deg, #a080ff, #398fe9);
      box-shadow: 0 0 65px rgba(137, 112, 255, .4);
      font-size: 21px;
      font-weight: 900;
      line-height: 1.4;
      z-index: 2;
    }

    .node {
      position: absolute;
      width: 66px;
      height: 66px;
      border: 1px solid rgba(163, 177, 255, .23);
      border-radius: 18px;
      display: grid;
      place-items: center;
      text-align: center;
      font-size: 9px;
      font-weight: 800;
      background: #151d3c;
      color: #e5eaff;
      z-index: 3;
    }

    .node-a { top: 12px; }
    .node-b { right: 4px; }
    .node-c { bottom: 12px; }
    .node-d { left: 4px; }

    .visual-tags {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 8px;
    }

    .tag {
      border: 1px solid var(--line);
      background: rgba(255,255,255,.035);
      border-radius: 30px;
      padding: 5px 12px;
      color: #bdc8e8;
      font-size: 10px;
    }

    .stats {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
      margin-top: 42px;
    }

    .stat {
      padding: 23px 15px;
      border: 1px solid var(--line);
      border-radius: 17px;
      text-align: center;
      background: rgba(19, 26, 53, .72);
    }

    .stat strong {
      display: block;
      font-size: 34px;
      line-height: 1.3;
      font-weight: 900;
      color: #b9a4ff;
      direction: ltr;
    }

    .stat span {
      display: block;
      font-size: 12px;
      color: var(--muted);
      margin-top: 5px;
    }

    .two-col {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .info-panel {
      border: 1px solid var(--line);
      border-radius: 21px;
      padding: 27px;
      background: linear-gradient(145deg, rgba(24, 32, 64, .8), rgba(13, 19, 41, .8));
    }

    .info-panel h3 {
      font-size: 19px;
      margin-bottom: 12px;
    }

    .info-panel p {
      color: var(--muted);
      font-size: 13px;
    }

    .info-panel ul {
      list-style: none;
      margin-top: 17px;
    }

    .info-panel li {
      padding: 8px 0;
      border-bottom: 1px solid var(--line);
      color: #d4dcf5;
      font-size: 12px;
    }

    .info-panel li:last-child {
      border-bottom: 0;
    }

    .info-panel li::before {
      content: "✓";
      color: var(--cyan);
      font-weight: 900;
      margin-left: 10px;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
    }

    .card {
      padding: 23px;
      border: 1px solid var(--line);
      border-radius: 19px;
      background: rgba(18, 25, 51, .75);
      transition: transform .2s, border-color .2s;
    }

    .card:hover {
      transform: translateY(-4px);
      border-color: rgba(155, 124, 255, .42);
    }

    .card-icon {
      width: 44px;
      height: 44px;
      display: grid;
      place-items: center;
      border-radius: 14px;
      background: rgba(145, 117, 255, .13);
      color: #c7b8ff;
      font-size: 19px;
      margin-bottom: 14px;
    }

    .card h3 {
      font-size: 16px;
      margin-bottom: 9px;
    }

    .card p {
      color: var(--muted);
      font-size: 12px;
    }

    .department-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 13px;
    }

    .department {
      display: flex;
      align-items: flex-start;
      gap: 15px;
      padding: 19px;
      border: 1px solid var(--line);
      border-radius: 17px;
      background: rgba(17, 24, 49, .78);
      transition: border-color .2s, transform .2s;
    }

    .department:hover {
      transform: translateY(-3px);
      border-color: rgba(155, 124, 255, .4);
    }

    .dept-code {
      flex: 0 0 48px;
      height: 48px;
      display: grid;
      place-items: center;
      border-radius: 14px;
      background: linear-gradient(140deg, rgba(151, 119, 255, .24), rgba(66, 154, 239, .15));
      color: #d2c5ff;
      font-size: 12px;
      font-weight: 900;
      direction: ltr;
    }

    .dept-content {
      flex: 1;
    }

    .dept-content h3 {
      font-size: 14px;
      margin-bottom: 4px;
    }

    .dept-content p {
      color: var(--muted);
      font-size: 11px;
      line-height: 1.8;
    }

    .dept-count {
      display: inline-block;
      margin-top: 8px;
      font-size: 10px;
      color: var(--cyan);
      border: 1px solid rgba(101, 222, 219, .2);
      padding: 2px 9px;
      border-radius: 30px;
    }

    .skills-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 11px;
    }

    .skill {
      padding: 13px 12px;
      border: 1px solid var(--line);
      border-radius: 12px;
      background: rgba(18, 25, 51, .72);
      color: #d5ddf5;
      font-size: 11px;
      text-align: center;
    }

    .laravel-panel {
      border: 1px solid rgba(155, 124, 255, .23);
      border-radius: 25px;
      padding: 32px;
      background:
        radial-gradient(circle at 90% 10%, rgba(155, 124, 255, .13), transparent 20rem),
        rgba(17, 24, 49, .8);
    }

    .laravel-panel h3 {
      font-size: 22px;
      margin-bottom: 10px;
    }

    .laravel-panel > p {
      color: var(--muted);
      font-size: 13px;
      max-width: 850px;
    }

    .technology-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
      margin-top: 25px;
    }

    .technology {
      padding: 16px;
      border: 1px solid var(--line);
      border-radius: 13px;
      background: rgba(255,255,255,.025);
    }

    .technology strong {
      display: block;
      font-size: 13px;
      margin-bottom: 4px;
    }

    .technology span {
      color: var(--muted);
      font-size: 10px;
    }

    .workflow {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 11px;
    }

    .workflow-step {
      padding: 19px 12px;
      text-align: center;
      border: 1px solid var(--line);
      border-radius: 16px;
      background: rgba(18, 25, 51, .75);
    }

    .workflow-step b {
      display: block;
      color: var(--purple);
      font-size: 10px;
      margin-bottom: 9px;
      direction: ltr;
    }

    .workflow-step strong {
      display: block;
      font-size: 13px;
    }

    .workflow-step span {
      display: block;
      font-size: 10px;
      color: var(--muted);
      margin-top: 7px;
    }

    .rdei-box {
      border: 1px solid rgba(101, 222, 219, .2);
      border-radius: 23px;
      padding: 29px;
      background: linear-gradient(140deg, rgba(17, 43, 61, .65), rgba(20, 24, 53, .8));
    }

    .rdei-box h3 {
      font-size: 22px;
      margin-bottom: 10px;
    }

    .rdei-box > p {
      color: var(--muted);
      font-size: 13px;
      margin-bottom: 22px;
    }

    .rdei-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
    }

    .rdei-item {
      padding: 15px;
      border: 1px solid var(--line);
      border-radius: 13px;
      background: rgba(255,255,255,.025);
    }

    .rdei-item strong {
      display: block;
      font-size: 12px;
      margin-bottom: 5px;
    }

    .rdei-item span {
      color: var(--muted);
      font-size: 10px;
    }

    .security-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 15px;
    }

    .security-card {
      padding: 21px;
      border-radius: 17px;
      border: 1px solid var(--line);
      background: rgba(18, 25, 51, .75);
    }

    .security-card h3 {
      font-size: 14px;
      margin-bottom: 8px;
    }

    .security-card p {
      color: var(--muted);
      font-size: 11px;
    }

    .status-note {
      margin-top: 23px;
      padding: 18px 20px;
      border: 1px solid rgba(101, 222, 219, .17);
      border-radius: 15px;
      background: rgba(101, 222, 219, .045);
      color: #c3d8e5;
      font-size: 11px;
    }

    .status-note strong {
      color: var(--cyan);
    }

    .cta {
      padding: 42px;
      border: 1px solid rgba(155, 124, 255, .25);
      border-radius: 25px;
      background:
        radial-gradient(circle at 10% 10%, rgba(155, 124, 255, .2), transparent 25rem),
        linear-gradient(130deg, #151d3c, #10172e);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 25px;
    }

    .cta h2 {
      font-size: 29px;
      margin-bottom: 9px;
    }

    .cta p {
      color: var(--muted);
      font-size: 13px;
      max-width: 730px;
    }

    .contact-panel {
      border: 1px solid var(--line);
      border-radius: 21px;
      background: rgba(18, 25, 51, .78);
      padding: 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
    }

    .contact-panel p {
      color: var(--muted);
      font-size: 13px;
    }

    .phone {
      display: inline-block;
      direction: ltr;
      font-weight: 900;
      font-size: 20px;
      color: #c5b6ff;
      margin-top: 5px;
    }

    footer {
      padding: 35px 0;
      border-top: 1px solid var(--line);
      color: var(--muted-2);
      font-size: 11px;
    }

    .footer-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      flex-wrap: wrap;
    }

    .footer-brand {
      color: white;
      font-weight: 900;
      letter-spacing: .12em;
    }

    .footer-note {
      max-width: 820px;
      margin-top: 14px;
      font-size: 10px;
      color: var(--muted-2);
    }

    @media (max-width: 950px) {
      .nav-links {
        display: none;
      }

      .hero-grid {
        grid-template-columns: 1fr;
      }

      .hero-visual {
        max-width: 650px;
        width: 100%;
        margin-inline: auto;
      }

      .cards {
        grid-template-columns: repeat(2, 1fr);
      }

      .skills-grid {
        grid-template-columns: repeat(3, 1fr);
      }
    }

    @media (max-width: 700px) {
      .section {
        padding: 65px 0;
      }

      .stats {
        grid-template-columns: repeat(2, 1fr);
      }

      .two-col,
      .department-grid,
      .technology-grid,
      .rdei-grid,
      .security-grid {
        grid-template-columns: 1fr;
      }

      .workflow {
        grid-template-columns: repeat(2, 1fr);
      }

      .cta,
      .contact-panel {
        flex-direction: column;
        align-items: flex-start;
        padding: 25px;
      }
    }

    @media (max-width: 500px) {
      .container {
        width: calc(100% - 28px);
      }

      .hero {
        padding: 65px 0;
      }

      .hero h1 {
        font-size: 42px;
      }

      .hero-copy {
        font-size: 14px;
      }

      .cards {
        grid-template-columns: 1fr;
      }

      .skills-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .hero-visual {
        min-height: 370px;
        padding: 18px;
      }

      .orbit-area {
        transform: scale(.88);
      }

      .nav-cta {
        padding: 9px 12px;
        font-size: 10px;
      }

      .brand-name {
        font-size: 12px;
      }

      .workflow {
        grid-template-columns: 1fr 1fr;
      }
    }
  </style>
</head>

<body>

  <!-- =========================================
       HEADER
  ========================================== -->

  <header class="topbar">
    <div class="container nav">

      <a href="#home" class="brand">
        <span class="brand-mark">A</span>

        <span class="brand-name">
          ASTROVA
          <small>ENGINEERING INTELLIGENCE</small>
        </span>
      </a>

      <nav class="nav-links">
        <a href="#about">عن المنظومة</a>
        <a href="#departments">الأقسام</a>
        <a href="#laravel">Laravel</a>
        <a href="#rdei">RDEI</a>
        <a href="#security">الأمن والحوكمة</a>
        <a href="#contact">التواصل</a>
      </nav>

      <a
        class="btn nav-cta"
        href="https://wa.me/201067357431?text=%D9%85%D8%B1%D8%AD%D8%A8%D8%A7%D9%8B%20Astrova%D8%8C%20%D8%A3%D8%B1%D8%BA%D8%A8%20%D9%81%D9%8A%20%D9%85%D8%B9%D8%B1%D9%81%D8%A9%20%D8%AA%D9%81%D8%A7%D8%B5%D9%8A%D9%84%20Universal%20Agent%20OS%20(UAOS).%20%D8%A7%D9%84%D9%85%D8%B5%D8%AF%D8%B1%3A%20GitHub."
        target="_blank"
        rel="noopener noreferrer"
      >
        اطلب النظام ↗
      </a>

    </div>
  </header>


  <main id="home">

    <!-- =========================================
         HERO
    ========================================== -->

    <section class="hero">
      <div class="container">

        <div class="hero-grid">

          <div>

            <div class="eyebrow">
              UNIVERSAL ENTERPRISE LARAVEL AI ENGINEERING ORGANIZATION
            </div>

            <h1>
              أكثر من مجرد
              <br>
              <span class="gradient">ذكاء اصطناعي.</span>
              <br>
              منظومة هندسية متكاملة.
            </h1>

            <p class="hero-copy">
              <strong>Universal Agent OS (UAOS)</strong>
              هي منظومة تشغيل وتنظيم هندسية متخصصة في مشاريع
              <strong>Laravel وPHP</strong>، صُممت لتجمع الوكلاء
              الهندسيين المتخصصين والمهارات المشتركة والبحث التقني
              والحوكمة وضمان الجودة داخل بيئة عمل واحدة.

              بدلاً من الاعتماد على وكيل ذكاء اصطناعي واحد لتنفيذ
              كل شيء، تعتمد UAOS على تنظيم هندسي متعدد التخصصات
              يغطي دورة حياة المشروع من اكتشاف المتطلبات وحتى
              الاختبارات والمراجعة والإغلاق.
            </p>

            <div class="hero-actions">

              <a
                class="btn"
                href="https://wa.me/201067357431?text=%D9%85%D8%B1%D8%AD%D8%A8%D8%A7%D9%8B%20Astrova%D8%8C%20%D8%A3%D8%B1%D8%BA%D8%A8%20%D9%81%D9%8A%20%D9%85%D8%B9%D8%B1%D9%81%D8%A9%20%D8%AA%D9%81%D8%A7%D8%B5%D9%8A%D9%84%20Universal%20Agent%20OS%20(UAOS).%20%D8%A7%D9%84%D9%85%D8%B5%D8%AF%D8%B1%3A%20GitHub."
                target="_blank"
                rel="noopener noreferrer"
              >
                تواصل لطلب UAOS ↗
              </a>

              <a href="#departments" class="btn btn-outline">
                اكتشف الأقسام الهندسية
              </a>

            </div>

            <p class="hero-note">
              منظومة هندسية متخصصة — Astrova
            </p>

          </div>


          <div class="hero-visual">

            <div class="visual-title">
              <span>UAOS SYSTEM ARCHITECTURE</span>
              <span class="visual-status">● ENGINEERING CORE</span>
            </div>

            <div class="orbit-area">

              <div class="orbit orbit-a"></div>
              <div class="orbit orbit-b"></div>
              <div class="orbit orbit-c"></div>

              <div class="core">
                UAOS
                <br>
                CORE
              </div>

              <div class="node node-a">112 AGENTS</div>
              <div class="node node-b">RDEI</div>
              <div class="node node-c">GOVERNANCE</div>
              <div class="node node-d">LARAVEL</div>

            </div>

            <div class="visual-tags">
              <span class="tag">Engineering</span>
              <span class="tag">Research</span>
              <span class="tag">Security</span>
              <span class="tag">Quality</span>
              <span class="tag">Orchestration</span>
            </div>

          </div>

        </div>


        <div class="stats">

          <div class="stat">
            <strong>112</strong>
            <span>وكيل هندسي متخصص</span>
          </div>

          <div class="stat">
            <strong>14</strong>
            <span>قسمًا هندسيًا</span>
          </div>

          <div class="stat">
            <strong>22</strong>
            <span>مهارة هندسية مشتركة</span>
          </div>

          <div class="stat">
            <strong>Laravel</strong>
            <span>التخصص الهندسي الأساسي</span>
          </div>

        </div>

      </div>
    </section>


    <!-- =========================================
         ABOUT
    ========================================== -->

    <section class="section" id="about">
      <div class="container">

        <div class="section-head">
          <div class="section-label">01 / ABOUT UAOS</div>

          <h2>
            ما هي <span class="gradient">Universal Agent OS؟</span>
          </h2>

          <p>
            UAOS ليست مجرد قائمة من الوكلاء أو مجموعة من التعليمات
            النصية. إنها تنظيم هندسي متعدد الأقسام، يهدف إلى تحويل
            الذكاء الاصطناعي إلى قوة عمل هندسية منظمة يمكن توجيهها
            وتنسيقها ومراجعة نتائجها.
          </p>
        </div>


        <div class="two-col">

          <div class="info-panel">

            <h3>الفكرة الأساسية</h3>

            <p>
              تطوير مشروع برمجي احترافي يحتاج إلى تخصصات متعددة
              تعمل معًا، وليس إلى مطور واحد يحاول معالجة كل جوانب
              المشروع في الوقت نفسه.

              لذلك تم بناء UAOS على فكرة المؤسسة الهندسية:
              أقسام متخصصة، أدوار واضحة، مهارات مشتركة،
              حوكمة، ومراحل عمل مترابطة.
            </p>

            <ul>
              <li>تنظيم الوكلاء وفق تخصصات ومسؤوليات محددة.</li>
              <li>توزيع المهام حسب طبيعتها ومتطلباتها.</li>
              <li>الفصل بين التحليل والتخطيط والتنفيذ والمراجعة.</li>
              <li>استخدام مهارات هندسية مشتركة بين الوكلاء.</li>
              <li>ربط المخرجات بالاختبارات والأدلة والمراجعات.</li>
            </ul>

          </div>


          <div class="info-panel">

            <h3>لماذا هذا النموذج مختلف؟</h3>

            <p>
              لأن الهدف ليس إنتاج أكبر عدد من الإجابات،
              وإنما تنظيم عملية هندسية قابلة للإدارة والتحقق.

              كل تخصص له دور داخل المنظومة، والعمل لا يعتمد
              على افتراض أن وكيلًا واحدًا يمتلك كل الخبرات
              المطلوبة لتنفيذ مشروع كامل.
            </p>

            <ul>
              <li>هيكل تنظيمي يضم 14 قسمًا.</li>
              <li>112 وكيلًا مسجلًا ضمن الهيكل الهندسي.</li>
              <li>22 مهارة مشتركة وفق آخر تحديث للمنظومة.</li>
              <li>تخصص أساسي في Laravel وPHP.</li>
              <li>اهتمام مركزي بالأمن والجودة والحوكمة.</li>
            </ul>

          </div>

        </div>

      </div>
    </section>


    <!-- =========================================
         DEPARTMENTS
    ========================================== -->

    <section class="section" id="departments">
      <div class="container">

        <div class="section-head">
          <div class="section-label">02 / ENGINEERING ORGANIZATION</div>

          <h2>
            14 قسمًا هندسيًا.
            <span class="gradient">112 وكيلًا متخصصًا.</span>
          </h2>

          <p>
            يتكون الهيكل التنظيمي لـ UAOS من أربعة عشر قسمًا،
            يغطي كل منها جانبًا محددًا من دورة حياة تطوير
            البرمجيات وإدارة الجودة والأمن والتوثيق.
          </p>
        </div>


        <div class="department-grid">

          <article class="department">
            <div class="dept-code">DEPT-A</div>
            <div class="dept-content">
              <h3>القيادة الهندسية والحوكمة</h3>
              <p>
                Engineering Leadership & Governance.
                مسؤول عن القيادة الهندسية، تنظيم المسؤوليات،
                حوكمة سير العمل، ضبط القرارات ومتابعة الالتزام
                بالمعايير المعتمدة.
              </p>
              <span class="dept-count">8 Agents</span>
            </div>
          </article>


          <article class="department">
            <div class="dept-code">DEPT-B</div>
            <div class="dept-content">
              <h3>اكتشاف المشاريع وتحليل المتطلبات</h3>
              <p>
                Project Discovery & Requirements.
                مسؤول عن فهم أهداف المشروع، اكتشاف المتطلبات،
                تحليل احتياجات أصحاب المصلحة وتحويلها إلى
                متطلبات واضحة قابلة للتنفيذ.
              </p>
              <span class="dept-count">7 Agents</span>
            </div>
          </article>


          <article class="department">
            <div class="dept-code">DEPT-C</div>
            <div class="dept-content">
              <h3>هندسة البرمجيات والمعمارية</h3>
              <p>
                Software Architecture & Design.
                مسؤول عن التصميم المعماري، تقسيم الأنظمة،
                تحديد الحدود والمسؤوليات، تصميم المكونات
                والعلاقات والاعتماديات.
              </p>
              <span class="dept-count">8 Agents</span>
            </div>
          </article>


          <article class="department">
            <div class="dept-code">DEPT-D</div>
            <div class="dept-content">
              <h3>هندسة Laravel وPHP</h3>
              <p>
                Laravel & PHP Engineering.
                القسم المتخصص في تطوير منطق التطبيقات،
                خدمات Laravel، طبقات التطبيق، المعالجة،
                التكاملات الداخلية وبناء الوظائف الخلفية.
              </p>
              <span class="dept-count">12 Agents</span>
            </div>
          </article>


          <article class="department">
            <div class="dept-code">DEPT-E</div>
            <div class="dept-content">
              <h3>هندسة قواعد البيانات والبيانات</h3>
              <p>
                Database & Data Engineering.
                مسؤول عن نمذجة البيانات، تصميم قواعد البيانات،
                العلاقات، سلامة البيانات، الاستعلامات،
                والترحيلات وإدارة التخزين.
              </p>
              <span class="dept-count">8 Agents</span>
            </div>
          </article>


          <article class="department">
            <div class="dept-code">DEPT-F</div>
            <div class="dept-content">
              <h3>الأمن والتشفير والامتثال</h3>
              <p>
                Security, Cryptography & Compliance.
                مسؤول عن مراجعة المخاطر الأمنية،
                حماية البيانات والأسرار، ضوابط الوصول،
                التشفير، العزل والامتثال للسياسات.
              </p>
              <span class="dept-count">12 Agents</span>
            </div>
          </article>


          <article class="department">
            <div class="dept-code">DEPT-G</div>
            <div class="dept-content">
              <h3>تجربة المستخدم والتصميم</h3>
              <p>
                UI/UX, Design Systems & Usability.
                مسؤول عن تجربة المستخدم، تصميم الواجهات،
                أنظمة التصميم، سهولة الاستخدام،
                الاتساق البصري وإمكانية الوصول.
              </p>
              <span class="dept-count">8 Agents</span>
            </div>
          </article>


          <article class="department">
            <div class="dept-code">DEPT-H</div>
            <div class="dept-content">
              <h3>هندسة الواجهات الأمامية</h3>
              <p>
                Frontend Engineering.
                مسؤول عن تطوير الواجهات الأمامية،
                تنظيم مكونات الواجهة، إدارة التفاعل
                وربط الواجهات بالخدمات الخلفية.
              </p>
              <span class="dept-count">8 Agents</span>
            </div>
          </article>


          <article class="department">
            <div class="dept-code">DEPT-I</div>
            <div class="dept-content">
              <h3>واجهات البرمجة والتكاملات</h3>
              <p>
                API & Integrations.
                مسؤول عن تصميم واجهات API،
                التكامل مع الخدمات الخارجية،
                عقود الاتصال وتبادل البيانات.
              </p>
              <span class="dept-count">6 Agents</span>
            </div>
          </article>


          <article class="department">
            <div class="dept-code">DEPT-J</div>
            <div class="dept-content">
              <h3>ضمان الجودة والاختبارات</h3>
              <p>
                Quality Assurance & Testing.
                مسؤول عن استراتيجيات الاختبار،
                التحقق من السلوك المتوقع،
                اختبارات الوحدات والتكامل والانحدار.
              </p>
              <span class="dept-count">8 Agents</span>
            </div>
          </article>


          <article class="department">
            <div class="dept-code">DEPT-K</div>
            <div class="dept-content">
              <h3>الأداء والتوسع والموثوقية</h3>
              <p>
                Performance, Scalability & Reliability.
                مسؤول عن تحليل الأداء، قابلية التوسع،
                الاعتمادية، معالجة الاختناقات
                وتحسين استهلاك الموارد.
              </p>
              <span class="dept-count">6 Agents</span>
            </div>
          </article>


          <article class="department">
            <div class="dept-code">DEPT-L</div>
            <div class="dept-content">
              <h3>البنية التحتية والإصدار والتشغيل</h3>
              <p>
                DevOps, Infrastructure & Release.
                مسؤول عن بيئات التطوير والتشغيل،
                إدارة الإصدارات، إجراءات النشر
                والبنية التحتية اللازمة للتطبيقات.
              </p>
              <span class="dept-count">7 Agents</span>
            </div>
          </article>


          <article class="department">
            <div class="dept-code">DEPT-M</div>
            <div class="dept-content">
              <h3>المراجعة المستقلة والتدقيق التقني</h3>
              <p>
                Independent Review & Technical Audit.
                مسؤول عن المراجعة المستقلة،
                فحص الالتزام بالمعايير،
                كشف الثغرات وتقييم الأدلة الفنية.
              </p>
              <span class="dept-count">8 Agents</span>
            </div>
          </article>


          <article class="department">
            <div class="dept-code">DEPT-N</div>
            <div class="dept-content">
              <h3>التوثيق الفني وإغلاق الأعمال</h3>
              <p>
                Technical Documentation & Closure.
                مسؤول عن التوثيق الفني،
                تنظيم مخرجات العمل، تقارير الإغلاق
                وتسجيل النتائج النهائية.
              </p>
              <span class="dept-count">6 Agents</span>
            </div>
          </article>

        </div>


        <div class="status-note">
          <strong>إجمالي الهيكل التنظيمي:</strong>
          112 وكيلاً موزعين على 14 قسمًا.
          الأعداد الموضحة تمثل توزيع الوكلاء المسجلين في الهيكل،
          ولا تعني أن كل وكيل يعمل بصورة مستقلة أو متزامنة في كل مهمة.
        </div>

      </div>
    </section>


    <!-- =========================================
         LARAVEL SPECIALIZATION
    ========================================== -->

    <section class="section" id="laravel">
      <div class="container">

        <div class="section-head">
          <div class="section-label">03 / LARAVEL ENGINEERING</div>

          <h2>
            تخصص هندسي في
            <span class="gradient">مشاريع Laravel</span>
          </h2>

          <p>
            UAOS موجهة أساسًا إلى هندسة وتطوير التطبيقات المبنية
            باستخدام Laravel وPHP. تم تنظيم الأقسام والمهارات
            لتغطية الجوانب المختلفة التي يحتاجها المشروع،
            من تحليل المتطلبات إلى المعمارية والتنفيذ والاختبار.
          </p>
        </div>


        <div class="laravel-panel">

          <h3>منظومة متخصصة في دورة حياة مشروع Laravel</h3>

          <p>
            لا يقتصر العمل على كتابة Controllers أو Models أو
            صفحات الواجهة. يشمل النهج الهندسي فهم النظام المطلوب،
            تصميم المعمارية، تحديد مسؤوليات الطبقات،
            بناء قواعد البيانات، تنفيذ الخدمات،
            تطوير الواجهات والتكاملات، ثم مراجعة الأمن والأداء
            واختبار النتائج.
          </p>


          <div class="technology-grid">

            <div class="technology">
              <strong>Laravel Application Engineering</strong>
              <span>بناء التطبيقات والخدمات ومنطق الأعمال.</span>
            </div>

            <div class="technology">
              <strong>PHP Engineering</strong>
              <span>هندسة الكود، الأنواع، البنية والتنظيم.</span>
            </div>

            <div class="technology">
              <strong>Service Container & Providers</strong>
              <span>إدارة الاعتماديات ومكونات التطبيق.</span>
            </div>

            <div class="technology">
              <strong>Routing & Middleware</strong>
              <span>المسارات والوسائط وضوابط الطلبات.</span>
            </div>

            <div class="technology">
              <strong>Eloquent & Database</strong>
              <span>النماذج والعلاقات والاستعلامات والترحيلات.</span>
            </div>

            <div class="technology">
              <strong>Queues & Async Processing</strong>
              <span>المهام الخلفية والمعالجة غير المتزامنة.</span>
            </div>

            <div class="technology">
              <strong>API Engineering</strong>
              <span>واجهات البرمجة والتكاملات.</span>
            </div>

            <div class="technology">
              <strong>Frontend & UI Engineering</strong>
              <span>الواجهات وتجربة المستخدم ومكونات العرض.</span>
            </div>

            <div class="technology">
              <strong>Testing & Quality</strong>
              <span>اختبارات الوظائف والتكامل والانحدار.</span>
            </div>

            <div class="technology">
              <strong>Security Engineering</strong>
              <span>الصلاحيات وحماية البيانات والأسرار.</span>
            </div>

            <div class="technology">
              <strong>Performance & Reliability</strong>
              <span>الأداء والتوسع والاعتمادية.</span>
            </div>

            <div class="technology">
              <strong>Deployment & Release</strong>
              <span>إدارة الإصدارات والتشغيل والبنية التحتية.</span>
            </div>

          </div>

        </div>

      </div>
    </section>


    <!-- =========================================
         SHARED SKILLS
    ========================================== -->

    <section class="section" id="skills">
      <div class="container">

        <div class="section-head">
          <div class="section-label">04 / SHARED ENGINEERING SKILLS</div>

          <h2>
            المهارات الهندسية
            <span class="gradient">المشتركة</span>
          </h2>

          <p>
            إلى جانب التخصصات الفردية، تعتمد UAOS على مكتبة
            مهارات هندسية مشتركة تهدف إلى توحيد المعايير
            والممارسات التي يحتاج إليها الوكلاء في تنفيذ أعمالهم.
          </p>
        </div>


        <div class="skills-grid">

          <div class="skill">Laravel Engineering Standards</div>
          <div class="skill">PHP Engineering Standards</div>
          <div class="skill">Software Architecture</div>
          <div class="skill">Database Engineering</div>
          <div class="skill">Security Engineering</div>
          <div class="skill">Cryptography Standards</div>
          <div class="skill">API Design Standards</div>
          <div class="skill">Frontend Engineering</div>
          <div class="skill">UI/UX Standards</div>
          <div class="skill">Testing Standards</div>
          <div class="skill">Code Quality Standards</div>
          <div class="skill">Performance Engineering</div>
          <div class="skill">Queue & Async Architecture</div>
          <div class="skill">Git & Review Practices</div>
          <div class="skill">DevOps & Release</div>
          <div class="skill">Documentation Standards</div>
          <div class="skill">Requirements Engineering</div>
          <div class="skill">Technical Audit</div>
          <div class="skill">Data Integrity</div>
          <div class="skill">Accessibility & Usability</div>
          <div class="skill">Vue & Inertia Standards</div>
          <div class="skill">Engineering Governance</div>

        </div>

        <div class="status-note">
          المهارات المشتركة تمثل مكتبة مرجعية موحدة للمعايير
          والممارسات الهندسية. ويظل تطبيق كل مهارة مرتبطًا
          بدور الوكيل وطبيعة المهمة والتقنيات المستخدمة في المشروع.
        </div>

      </div>
    </section>


    <!-- =========================================
         RDEI
    ========================================== -->

    <section class="section" id="rdei">
      <div class="container">

        <div class="section-head">
          <div class="section-label">05 / RESEARCH-DRIVEN ENGINEERING INTELLIGENCE</div>

          <h2>
            RDEI — ذكاء البحث
            <span class="gradient">الهندسي والمعرفة</span>
          </h2>

          <p>
            Research-Driven Engineering Intelligence هو مسار
            تطوير معرفي داخل UAOS يهدف إلى جعل القرارات الهندسية
            قائمة على البحث والمصادر والأدلة، بدلاً من الاعتماد
            على معلومات غير موثقة أو افتراضات غير مختبرة.
          </p>
        </div>


        <div class="rdei-box">

          <h3>من البحث إلى المعرفة الهندسية القابلة للمراجعة</h3>

          <p>
            يستهدف RDEI تنظيم دورة البحث: تحديد عمق البحث المناسب،
            التعامل مع المصادر، تقييم موثوقيتها وحداثتها،
            استخراج الأدلة والادعاءات، اكتشاف التعارضات،
            مقارنة الخيارات التقنية، وتجميع النتائج في ملفات
            بحثية يمكن الرجوع إليها.
          </p>


          <div class="rdei-grid">

            <div class="rdei-item">
              <strong>Source Registry</strong>
              <span>إدارة تعريفات المصادر ومعلوماتها.</span>
            </div>

            <div class="rdei-item">
              <strong>Source Trust</strong>
              <span>تقييم الثقة والأهلية للمصادر.</span>
            </div>

            <div class="rdei-item">
              <strong>Freshness Intelligence</strong>
              <span>تقييم حداثة المعلومات وتغيرها.</span>
            </div>

            <div class="rdei-item">
              <strong>Evidence Extraction</strong>
              <span>استخراج الأدلة والادعاءات وربطها بمصادرها.</span>
            </div>

            <div class="rdei-item">
              <strong>Research Depth</strong>
              <span>تحديد مستويات عمق البحث من L0 إلى L4.</span>
            </div>

            <div class="rdei-item">
              <strong>Domain Intelligence</strong>
              <span>استراتيجيات بحث متخصصة في المجالات المختلفة.</span>
            </div>

            <div class="rdei-item">
              <strong>Technology Comparison</strong>
              <span>مقارنة التقنيات وفق معايير متعددة.</span>
            </div>

            <div class="rdei-item">
              <strong>Conflict Detection</strong>
              <span>اكتشاف التعارضات بين المعلومات والأدلة.</span>
            </div>

            <div class="rdei-item">
              <strong>Research Dossiers</strong>
              <span>تجميع النتائج في ملفات بحثية منظمة.</span>
            </div>

          </div>


          <div class="status-note">
            <strong>حالة التطوير:</strong>
            تم قبول مراحل RDEI من 4.1 إلى 4.5 وفق تقارير
            المشروع. ويجري استكمال مرحلة التكامل التشغيلي
            4.6 ثم التحقق الشامل والإغلاق في 4.7.
            لذلك لا تُعرض هنا جميع قدرات RDEI باعتبارها
            قد اجتازت بالفعل التحقق التشغيلي النهائي.
          </div>

        </div>

      </div>
    </section>


    <!-- =========================================
         ENGINEERING WORKFLOW
    ========================================== -->

    <section class="section" id="workflow">
      <div class="container">

        <div class="section-head">
          <div class="section-label">06 / ENGINEERING WORKFLOW</div>

          <h2>
            دورة العمل
            <span class="gradient">الهندسية</span>
          </h2>

          <p>
            تعتمد UAOS منهجية تفصل بين تحديد المطلوب،
            والتخطيط، والتنفيذ، والاختبار، وإغلاق العمل.
            الهدف هو تقليل القرارات العشوائية وجعل كل مرحلة
            قابلة للمراجعة والاعتماد.
          </p>
        </div>


        <div class="workflow">

          <div class="workflow-step">
            <b>STAGE 01</b>
            <strong>Specification</strong>
            <span>تحليل المتطلبات وإعداد المواصفات.</span>
          </div>

          <div class="workflow-step">
            <b>STAGE 02</b>
            <strong>Atomic Tasks</strong>
            <span>تقسيم العمل إلى مهام محددة.</span>
          </div>

          <div class="workflow-step">
            <b>STAGE 03</b>
            <strong>Implementation Plans</strong>
            <span>إعداد خطط التنفيذ التفصيلية.</span>
          </div>

          <div class="workflow-step">
            <b>STAGE 04</b>
            <strong>Code & Tests</strong>
            <span>تنفيذ الكود والاختبارات.</span>
          </div>

          <div class="workflow-step">
            <b>STAGE 05</b>
            <strong>Closure</strong>
            <span>التحقق النهائي وإغلاق العمل بالأدلة.</span>
          </div>

        </div>


        <div class="two-col" style="margin-top:22px">

          <div class="info-panel">
            <h3>الموافقات والتحكم</h3>
            <p>
              تعتمد المنهجية على فصل الموافقة على المواصفات
              عن الموافقة على خطط التنفيذ، وعدم الانتقال
              إلى كتابة الكود قبل الحصول على التصريح المطلوب
              وفق ضوابط المشروع.
            </p>
          </div>

          <div class="info-panel">
            <h3>التنفيذ القابل للمراجعة</h3>
            <p>
              لا يُعامل اكتمال المهمة باعتباره مجرد إنتاج
              ملفات أو كود. يجب أن تكون هناك اختبارات
              ونتائج مراجعة وأدلة تدعم قرار الإغلاق.
            </p>
          </div>

        </div>

      </div>
    </section>


    <!-- =========================================
         SECURITY AND GOVERNANCE
    ========================================== -->

    <section class="section" id="security">
      <div class="container">

        <div class="section-head">
          <div class="section-label">07 / SECURITY & ENTERPRISE GOVERNANCE</div>

          <h2>
            الأمن والحوكمة
            <span class="gradient">جزء من الهندسة</span>
          </h2>

          <p>
            الحماية ليست إضافة تجميلية. تستهدف UAOS بناء
            ضوابط أمنية وحوكمية تحكم طريقة الوصول إلى البيانات،
            والتعامل مع المدخلات، وتنفيذ المهام،
            ومراجعة القرارات والنتائج.
          </p>
        </div>


        <div class="security-grid">

          <article class="security-card">
            <h3>عزل المسؤوليات والبيانات</h3>
            <p>
              تصميم الحدود بين المكونات والمهام والصلاحيات
              للحد من الوصول غير المصرح به.
            </p>
          </article>

          <article class="security-card">
            <h3>إدارة الصلاحيات</h3>
            <p>
              ربط تنفيذ العمليات بالصلاحيات والتفويضات
              والضوابط المعتمدة.
            </p>
          </article>

          <article class="security-card">
            <h3>حماية الأسرار</h3>
            <p>
              التعامل المنضبط مع مفاتيح API وبيانات
              الاعتماد والمعلومات الحساسة.
            </p>
          </article>

          <article class="security-card">
            <h3>التشفير وحماية البيانات</h3>
            <p>
              تضمين متطلبات التشفير وسلامة البيانات
              ضمن السياسات الهندسية.
            </p>
          </article>

          <article class="security-card">
            <h3>مقاومة المدخلات الخبيثة</h3>
            <p>
              التعامل مع المحتوى الخارجي باعتباره بيانات
              غير موثوقة، والتحقق منه قبل استخدامه.
            </p>
          </article>

          <article class="security-card">
            <h3>التدقيق والتتبع</h3>
            <p>
              الاهتمام بتسجيل القرارات والأحداث
              والأدلة اللازمة للمراجعة.
            </p>
          </article>

          <article class="security-card">
            <h3>التحقق قبل التنفيذ</h3>
            <p>
              استخدام بوابات وضوابط تمنع تجاوز
              الشروط المطلوبة قبل الانتقال بين المراحل.
            </p>
          </article>

          <article class="security-card">
            <h3>الاختبارات الأمنية</h3>
            <p>
              تضمين التحقق الأمني ضمن دورة الجودة
              ومراجعة المخاطر والثغرات.
            </p>
          </article>

          <article class="security-card">
            <h3>التوقف الآمن</h3>
            <p>
              عدم تجاوز الضوابط عند فشل التحقق
              أو عدم كفاية المعلومات المطلوبة.
            </p>
          </article>

        </div>


        <div class="status-note">
          <strong>تنويه مهم:</strong>
          هذه المبادئ تمثل متطلبات التصميم والحوكمة المستهدفة.
          لا تعني أن الصفحة تمثل شهادة أمنية أو ضمانًا مطلقًا
          ضد الاختراق. إثبات مستوى الحماية يتطلب اختبارات
          وتقييمات أمنية وتشغيلية فعلية.
        </div>

      </div>
    </section>


    <!-- =========================================
         QUALITY
    ========================================== -->

    <section class="section" id="quality">
      <div class="container">

        <div class="section-head">
          <div class="section-label">08 / QUALITY ENGINEERING</div>

          <h2>
            الجودة ليست مرحلة أخيرة.
            <span class="gradient">إنها مسؤولية مستمرة.</span>
          </h2>

          <p>
            تعمل أقسام الجودة والمراجعة والأداء والأمن
            ضمن الرؤية الهندسية للمنظومة، بهدف تقليل المخاطر
            وتحسين موثوقية المخرجات.
          </p>
        </div>


        <div class="cards">

          <article class="card">
            <div class="card-icon">✓</div>
            <h3>اختبارات الوظائف</h3>
            <p>
              التحقق من أن الوظائف المنفذة تحقق
              السلوك المتوقع وفق المتطلبات.
            </p>
          </article>

          <article class="card">
            <div class="card-icon">⌕</div>
            <h3>المراجعة التقنية</h3>
            <p>
              مراجعة بنية الكود والاعتماديات
              والالتزام بالمعايير الهندسية.
            </p>
          </article>

          <article class="card">
            <div class="card-icon">⛨</div>
            <h3>المراجعة الأمنية</h3>
            <p>
              فحص المخاطر المتعلقة بالمدخلات
              والصلاحيات والبيانات والتكاملات.
            </p>
          </article>

          <article class="card">
            <div class="card-icon">↗</div>
            <h3>الأداء والاعتمادية</h3>
            <p>
              تحليل الأداء وقابلية التوسع
              ومعالجة نقاط الاختناق.
            </p>
          </article>

          <article class="card">
            <div class="card-icon">▤</div>
            <h3>التوثيق الفني</h3>
            <p>
              تنظيم المعرفة والقرارات والمخرجات
              اللازمة لاستمرارية العمل.
            </p>
          </article>

          <article class="card">
            <div class="card-icon">◎</div>
            <h3>الإغلاق بالأدلة</h3>
            <p>
              دعم قرارات قبول وإغلاق الأعمال
              بنتائج الاختبارات والمراجعة.
            </p>
          </article>

        </div>

      </div>
    </section>


    <!-- =========================================
         FUTURE DIRECTION
    ========================================== -->

    <section class="section" id="future">
      <div class="container">

        <div class="section-head">
          <div class="section-label">09 / EVOLUTION</div>

          <h2>
            منظومة قابلة
            <span class="gradient">للتطور والتوسع</span>
          </h2>

          <p>
            تمثل UAOS أساسًا لمنظومة هندسية قابلة لإضافة
            قدرات جديدة مستقبلًا، مع الحفاظ على الفصل
            بين المكونات والحوكمة والمسؤوليات.
          </p>
        </div>


        <div class="two-col">

          <div class="info-panel">
            <h3>توسيع القدرات الذكية</h3>
            <p>
              من الاتجاهات المستقبلية الممكنة تطوير طبقة
              ذكاء اصطناعي مركزية أكثر تقدمًا لدعم الاستدلال
              والتخطيط والتقييم والتعلم من الخبرات السابقة،
              مع استمرار خضوعها لسياسات النظام وصلاحياته.
            </p>
          </div>

          <div class="info-panel">
            <h3>التوسع الهندسي</h3>
            <p>
              يمكن تطوير قدرات المنظومة لتخدم احتياجات
              مشاريع Laravel مختلفة، وإضافة تكاملات
              وأدوات ومهارات جديدة وفق متطلبات كل مشروع
              دون خلط التخصصات أو إضعاف الحوكمة.
            </p>
          </div>

        </div>

      </div>
    </section>


    <!-- =========================================
         CTA
    ========================================== -->

    <section class="section">
      <div class="container">

        <div class="cta">

          <div>
            <div class="section-label">WORK WITH ASTROVA</div>

            <h2>
              هل تريد معرفة المزيد عن
              <span class="gradient">UAOS؟</span>
            </h2>

            <p>
              تواصل معنا لمعرفة تفاصيل المنظومة،
              نطاق قدراتها، طريقة الحصول عليها،
              وشروط الاستخدام والترخيص.
            </p>
          </div>

          <a
            class="btn"
            href="https://wa.me/201067357431?text=%D9%85%D8%B1%D8%AD%D8%A8%D8%A7%D9%8B%20Astrova%D8%8C%20%D8%A3%D8%B1%D8%BA%D8%A8%20%D9%81%D9%8A%20%D9%85%D8%B9%D8%B1%D9%81%D8%A9%20%D8%AA%D9%81%D8%A7%D8%B5%D9%8A%D9%84%20Universal%20Agent%20OS%20(UAOS).%20%D8%A7%D9%84%D9%85%D8%B5%D8%AF%D8%B1%3A%20GitHub."
            target="_blank"
            rel="noopener noreferrer"
          >
            تواصل عبر واتساب ↗
          </a>

        </div>

      </div>
    </section>


    <!-- =========================================
         CONTACT
    ========================================== -->

    <section class="section" id="contact">
      <div class="container">

        <div class="section-head">
          <div class="section-label">10 / CONTACT</div>

          <h2>
            تواصل مع <span class="gradient">Astrova</span>
          </h2>

          <p>
            للاستفسارات المتعلقة بمنظومة Universal Agent OS،
            وطلب معلومات عن نطاق النظام وشروط الحصول عليه.
          </p>
        </div>


        <div class="contact-panel">

          <div>
            <p>WhatsApp / Direct Contact</p>

            <a
              class="phone"
              href="https://wa.me/201067357431"
              target="_blank"
              rel="noopener noreferrer"
            >
              +20 106 735 7431
            </a>
          </div>

          <a
            class="btn"
            href="https://wa.me/201067357431?text=%D9%85%D8%B1%D8%AD%D8%A8%D8%A7%D9%8B%20Astrova%D8%8C%20%D8%A3%D8%B1%D8%BA%D8%A8%20%D9%81%D9%8A%20%D9%85%D8%B9%D8%B1%D9%81%D8%A9%20%D8%AA%D9%81%D8%A7%D8%B5%D9%8A%D9%84%20Universal%20Agent%20OS%20(UAOS).%20%D8%A7%D9%84%D9%85%D8%B5%D8%AF%D8%B1%3A%20GitHub."
            target="_blank"
            rel="noopener noreferrer"
          >
            أرسل استفسارك الآن ↗
          </a>

        </div>

      </div>
    </section>

  </main>


  <!-- =========================================
       FOOTER
  ========================================== -->

  <footer>
    <div class="container">

      <div class="footer-inner">

        <div>
          <span class="footer-brand">ASTROVA</span>
          <span> © 2026 — جميع الحقوق محفوظة</span>
        </div>

        <div>
          Universal Agent OS (UAOS)
        </div>

      </div>


      <p class="footer-note">
        Universal Agent OS هي منظومة هندسية متخصصة في مشاريع Laravel.
        هذه الصفحة تعريفية عامة ولا تتضمن الشيفرة المصدرية
        أو ملفات النظام أو الوثائق الداخلية أو بياناته التشغيلية.
        لا تمثل المعلومات الواردة شهادة اعتماد أمني أو ضمانًا
        مطلقًا للأداء أو خلو النظام من الأخطاء.
        تخضع تفاصيل الإتاحة والترخيص ونطاق الاستخدام
        للاتفاق المباشر مع Astrova.
      </p>

    </div>
  </footer>

</body>
</html>
