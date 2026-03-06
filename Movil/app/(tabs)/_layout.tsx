import Feather from "@expo/vector-icons/Feather";
import Ionicons from "@expo/vector-icons/Ionicons";
import { BlurView } from "expo-blur";
import { Tabs } from "expo-router";
import React, { useRef } from "react";
import {
  Animated,
  Image,
  Platform,
  TouchableWithoutFeedback,
} from "react-native";
import { SafeAreaView, useSafeAreaInsets } from "react-native-safe-area-context";

const TAB_HEIGHT = 72;

export default function tabsLayout() {
  const insets = useSafeAreaInsets();

  const bottom = Math.max(insets.bottom, 10) + 4;

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: "#fff" }} edges={["top"]}>
      <Tabs
        screenOptions={{
          headerShown: false,

          tabBarActiveTintColor: "#2f9d48",
          tabBarInactiveTintColor: "#111827",

          sceneContainerStyle: {
            paddingBottom: TAB_HEIGHT + bottom + 14,
            backgroundColor: "#fff",
          },

          tabBarBackground: () =>
            Platform.OS === "ios" ? (
              <BlurView
                intensity={35}
                tint="light"
                style={{
                  flex: 1,
                  borderRadius: 40,
                  overflow: "hidden",
                }}
              />
            ) : null,

          tabBarStyle: {
            position: "absolute",

            left: 0,
            right: 0,
            marginHorizontal: 18,
            bottom: bottom,

            height: TAB_HEIGHT,
            borderRadius: 40,
            // overflow: "hidden",

            backgroundColor:
              Platform.OS === "android"
                ? "rgba(255,255,255,0.85)"
                : "transparent",

            borderWidth: 1,
            borderColor: "rgba(0,0,0,0.08)",

            shadowColor: "#000",
            shadowOpacity: 0.1,
            shadowOffset: { width: 0, height: 10 },
            shadowRadius: 18,
            elevation: 8,

            paddingTop: 8,
            paddingBottom: 10,
          },

          tabBarLabelStyle: {
            fontSize: 12,
            marginBottom: 2,
          },
        }}
      >
        <Tabs.Screen
          name="Home/index"
          options={{
            title: "Inicio",
            tabBarIcon: ({ color }) => (
              <Ionicons name="home-outline" size={26} color={color} />
            ),
          }}
        />

        <Tabs.Screen
          name="Chats/index"
          options={{
            title: "Chats",
            tabBarIcon: ({ color }) => (
              <Feather name="message-circle" size={26} color={color} />
            ),
          }}
        />

        <Tabs.Screen
          name="Vender/index"
          options={{
            title: "Vender",
            tabBarButton: (props) => <FloatButtom {...props} />,
          }}
        />

        <Tabs.Screen
          name="Favoritos/index"
          options={{
            title: "Favoritos",
            tabBarIcon: ({ color }) => (
              <Ionicons name="heart-outline" size={24} color={color} />
            ),
          }}
        />

        <Tabs.Screen
          name="Configuracion/index"
          options={{
            title: "Config",
            tabBarIcon: ({ color }) => (
              <Feather name="settings" size={24} color={color} />
            ),
          }}
        />
      </Tabs>
    </SafeAreaView>
  );
}

/** Botón flotante con micro-animación “bounce” */
function FloatButtom({ onPress }: { onPress?: () => void }) {
  const scale = useRef(new Animated.Value(1)).current;

  const pressIn = () => {
    Animated.spring(scale, {
      toValue: 0.93,
      useNativeDriver: true,
      speed: 22,
      bounciness: 6,
    }).start();
  };

  const pressOut = () => {
    Animated.spring(scale, {
      toValue: 1,
      useNativeDriver: true,
      speed: 22,
      bounciness: 6,
    }).start();
  };

  return (
    <TouchableWithoutFeedback
      onPress={onPress}
      onPressIn={pressIn}
      onPressOut={pressOut}
    >
      <Animated.View
        style={{
          transform: [{ scale }],

          width: 66,
          height: 66,
          borderRadius: 40,
          backgroundColor: "#2f9d48",
          justifyContent: "center",
          alignItems: "center",

          // flota encima de la barra, sin descuadrar pantallas
          marginTop: -28,
          marginLeft: 5,

          shadowColor: "#000",
          shadowOpacity: 0.25,
          shadowRadius: 10,
          shadowOffset: { width: 0, height: 6 },
          elevation: 10,
        }}
      >
        <Image
          source={require("../../assets/images/logo1.png")}
          resizeMode="contain"
          style={{ width: 78, height: 110, top: -3 }}
        />
      </Animated.View>
    </TouchableWithoutFeedback>
  );
}
