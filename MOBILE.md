# IBIG FactPro — Build Mobile (Capacitor)

## Prérequis
```bash
npm install @capacitor/core @capacitor/cli @capacitor/geolocation @capacitor/status-bar @capacitor/splash-screen
npx cap init
```

## Android
```bash
npm run build
npx cap add android
npx cap sync android
npx cap open android   # ouvre Android Studio
```

## iOS (Mac requis)
```bash
npx cap add ios
npx cap sync ios
npx cap open ios       # ouvre Xcode
```

## Live reload (dev)
```bash
npx cap run android --livereload --external
```

## Permissions requises
- android/app/src/main/AndroidManifest.xml : ACCESS_FINE_LOCATION, ACCESS_COARSE_LOCATION
- ios/App/App/Info.plist : NSLocationWhenInUseUsageDescription
