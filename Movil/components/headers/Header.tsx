import React from "react";
import {
  Image,
  ImageSourcePropType,
  Platform,
  StatusBar,
  Text,
  View,
  ViewProps,
  useWindowDimensions,
} from "react-native";

type ColorKey =
  | "primary"
  | "secondary"
  | "tertiary"
  | "quaternary"
  | "quinary"
  | "sextary";
type Variant = "normal" | "text-only" | "icon-only";

interface HeaderProps extends ViewProps {
  children?: React.ReactNode;
  color?: ColorKey;
  txtColor?: ColorKey;
  className?: string;
  variant?: Variant;
  source?: ImageSourcePropType;

  // Puedes dejar FontText para otras clases (bold, tracking, etc)
  FontText?: string;

  showLogo?: boolean;
  height?: number;
  radius?: number;

  // NUEVOS: control real
  logoSize?: number;
  titleSize?: number;

  // si alguna pantalla NO quiere padding por status bar
  includeStatusBarPadding?: boolean;
}

export const HEADER_DEFAULT_HEIGHT = 220;

const Header = React.forwardRef<View, HeaderProps>(
  (
    {
      children,
      color = "primary",
      txtColor = "primary",
      className = "",
      variant = "normal",
      source,
      FontText = "font-bold",
      showLogo = false,
      height = HEADER_DEFAULT_HEIGHT,
      radius = 60,
      logoSize,
      titleSize = 40,
      includeStatusBarPadding = true,
      ...rest
    },
    ref
  ) => {
    const { width } = useWindowDimensions();

    const textColor =
      {
        primary: "text-primary-50",
        secondary: "text-secondary-500",
        tertiary: "text-tertiary-900",
        quaternary: "text-quaternary-50",
        quinary: "text-quinary-50",
        sextary: "text-sextary-900",
      }[txtColor] ?? "text-white";

    const headerColor =
      {
        primary: "bg-primary-400",
        secondary: "bg-secondary-950",
        tertiary: "bg-tertiary-50",
        quaternary: "bg-quaternary-700",
        quinary: "bg-quinary-600",
        sextary: "bg-sextary-500",
      }[color] ?? "bg-[#2DC75C]";

    // Logo responsivo (si no lo mandas por prop)
    const computedLogo = logoSize ?? Math.max(96, Math.min(170, width * 0.32));

    // Alto del status bar (aprox en iPhone)
    const statusBarPadding = !includeStatusBarPadding
      ? 0
      : Platform.OS === "ios"
      ? 44
      : StatusBar.currentHeight ?? 0;

    const containerClass = `absolute top-0 left-0 right-0 ${headerColor} items-center justify-center ${className}`;

    return (
      <View ref={ref} {...rest}>
        {(variant === "normal" || variant === "text-only") && (
          <View
            className={containerClass}
            style={{
              height,
              paddingTop: statusBarPadding,
              borderBottomLeftRadius: radius,
              borderBottomRightRadius: radius,
            }}
          >
            {showLogo && (
              <Image
                source={source ?? require("../../assets/images/logo.png")}
                resizeMode="contain"
                style={{
                  width: computedLogo,
                  height: computedLogo,
                  marginBottom: 8,
                }}
              />
            )}

            {typeof children === "string" ? (
              <Text
                className={`${FontText} ${textColor} text-center`}
                style={{ fontSize: titleSize }}
              >
                {children}
              </Text>
            ) : (
              children
            )}
          </View>
        )}

        {variant === "icon-only" && (
          <View
            className={containerClass}
            style={{
              height,
              paddingTop: statusBarPadding,
              borderBottomLeftRadius: radius,
              borderBottomRightRadius: radius,
            }}
          >
            <Image
              source={source ?? require("../../assets/images/logo.png")}
              resizeMode="contain"
              style={{ width: computedLogo, height: computedLogo }}
            />
          </View>
        )}
      </View>
    );
  }
);

Header.displayName = "Header";
export default Header;
