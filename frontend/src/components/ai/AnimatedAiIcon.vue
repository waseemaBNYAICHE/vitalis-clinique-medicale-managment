<template>
  <svg
    class="ai-icon"
    viewBox="0 0 100 100"
    xmlns="http://www.w3.org/2000/svg"
    role="img"
    aria-label="Intelligence artificielle"
  >
    <circle class="background" cx="50" cy="50" r="43" />
    <circle class="pulse" cx="50" cy="50" r="36" />

    <g class="brain">
      <path
        d="M48 29
           C40 22 29 27 30 37
           C21 40 22 54 31 57
           C27 67 38 75 48 68 Z"
      />

      <path
        d="M52 29
           C60 22 71 27 70 37
           C79 40 78 54 69 57
           C73 67 62 75 52 68 Z"
      />

      <path d="M50 28V71" />
      <path d="M36 40C42 40 45 44 45 49" />
      <path d="M64 40C58 40 55 44 55 49" />
      <path d="M35 58C41 58 44 55 44 51" />
      <path d="M65 58C59 58 56 55 56 51" />
    </g>

    <g class="circuits">
      <path d="M30 35H19V25" />
      <path d="M70 35H81V25" />
      <path d="M30 62H19V73" />
      <path d="M70 62H81V73" />

      <circle cx="19" cy="24" r="3" />
      <circle cx="81" cy="24" r="3" />
      <circle cx="19" cy="74" r="3" />
      <circle cx="81" cy="74" r="3" />
    </g>

    <circle class="orbit-dot" cx="50" cy="14" r="3" />
  </svg>
</template>

<style scoped>
.ai-icon {
  width: 100%;
  height: 100%;
  display: block;
  overflow: visible;
}

.background {
  fill: #2447e8;
}

.pulse {
  fill: none;
  stroke: #32d5df;
  stroke-width: 2;
  transform-origin: center;
  animation: pulse-animation 2s ease-out infinite;
}

.brain {
  fill: none;
  stroke: white;
  stroke-width: 3;
  stroke-linecap: round;
  stroke-linejoin: round;
  transform-origin: center;
  animation: brain-animation 2.4s ease-in-out infinite;
}

.circuits {
  fill: #32d5df;
  stroke: #32d5df;
  stroke-width: 2;
  stroke-linecap: round;
  animation: circuit-animation 1.4s ease-in-out infinite alternate;
}

.orbit-dot {
  fill: white;
  transform-origin: 50px 50px;
  animation: orbit-animation 3s linear infinite;
}

@keyframes pulse-animation {
  from {
    opacity: 0.8;
    transform: scale(0.8);
  }

  to {
    opacity: 0;
    transform: scale(1.35);
  }
}

@keyframes brain-animation {
  0%,
  100% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.07);
  }
}

@keyframes circuit-animation {
  from {
    opacity: 0.35;
  }

  to {
    opacity: 1;
  }
}

@keyframes orbit-animation {
  to {
    transform: rotate(360deg);
  }
}
</style>