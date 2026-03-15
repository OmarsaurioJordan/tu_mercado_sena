import CustomButton from "@/components/buttons/CustomButton";
import { AntDesign } from "@expo/vector-icons";
import { useRouter } from "expo-router";
import React from "react";
import {
  KeyboardAvoidingView,
  Platform,
  Pressable,
  ScrollView,
  Text,
  View,
} from "react-native";
import { SafeAreaView, useSafeAreaInsets } from "react-native-safe-area-context";

const ChatID = () => {
  const router = useRouter();
  const insets = useSafeAreaInsets();

  const HEADER_PAD_TOP = Math.max(insets.top, 10);

  const bottomPad =
    Platform.OS === "ios"
      ? Math.min(insets.bottom, 16)
      : Math.max(insets.bottom, 8);

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: "#FFF" }} edges={["top", "bottom"]}>
      <KeyboardAvoidingView
        style={{ flex: 1 }}
        behavior={Platform.OS === "ios" ? "padding" : undefined}
      >
        {/* HEADER */}
        <View
          style={{
            paddingTop: HEADER_PAD_TOP - 15,
            flexDirection: "row",
            alignItems: "center",
            paddingHorizontal: 12,
            paddingVertical: 10,
            borderBottomWidth: 1,
            borderBottomColor: "#E5E5E5",
            backgroundColor: "#FFF",
          }}
        >
          <Pressable onPress={() => router.push("/(tabs)/Chats")}>
            <AntDesign name="arrow-left" size={24} color="#2FBF2F" />
          </Pressable>

          <View
            style={{
              width: 42,
              height: 42,
              borderRadius: 21,
              borderWidth: 2,
              borderColor: "#2FBF2F",
              alignItems: "center",
              justifyContent: "center",
              marginLeft: 10,
            }}
          >
            <AntDesign name="user" size={22} color="#2FBF2F" />
          </View>

          <Text
            style={{
              marginLeft: 10,
              fontSize: 16,
              fontWeight: "600",
              color: "#000",
              flexShrink: 1,
            }}
            numberOfLines={1}
          >
            Yhonaikerson Mejia
          </Text>
        </View>

        {/* MENSAJES */}
        <ScrollView
          style={{ flex: 1 }}
          contentContainerStyle={{
            padding: 12,
            paddingBottom: 12,
          }}
          showsVerticalScrollIndicator={false}
          keyboardShouldPersistTaps="handled"
        >
          <CustomButton variant="chat-bubble" isOwner={false}>
            Hola, como esta, me interesa en prduto
          </CustomButton>

          <CustomButton variant="chat-bubble" isOwner>
            Sisas bro
          </CustomButton>

          <View style={{ height: 12 }} />
        </ScrollView>

        {/* INPUT (abajo) */}
        <View style={{ paddingBottom: bottomPad }}>
          <CustomButton
            variant="chat-input"
            onSendMessage={(msg) => {
              console.log("Mensaje:", msg);
            }}
          />
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
};

export default ChatID;
