import { Ionicons } from "@expo/vector-icons";
import { useRouter } from "expo-router";
import React from "react";
import { Image, ScrollView, Text, TouchableOpacity, View } from "react-native";

export default function ContactosBloqueados() {
  const router = useRouter();

  return (
    <View className="flex-1 bg-white px-4 pt-6">

      {/* HEADER */}
      <View className="flex-row items-center mt-6 mb-6">
        <TouchableOpacity onPress={() => router.back()}>
          <Ionicons name="arrow-back" size={24} color="black" />
        </TouchableOpacity>
        <Text className="text-lg font-bold ml-3 flex-1 text-center">
          Contactos bloqueados
        </Text>
      </View>

      {/* LISTA SCROLL */}
      <ScrollView
        showsVerticalScrollIndicator={false}
        contentContainerStyle={{ paddingBottom: 20 }}
      >

        {/* CONTACTO 1 */}
        <View className="flex-row items-center justify-between mt-3 mb-4">
          <View className="flex-row items-center gap-3">
            <Image
              source={{ uri: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTGbS9-IaQY1X5uYOtncYbx2U4kFZPPnkg-WA&s" }}
              className="w-12 h-12 rounded-full"
            />
            <View>
              <Text className="font-bold text-base">Gintoki</Text>
              <Text className="text-gray-500 text-sm">
                Bloqueado 2 Feb 2025
              </Text>
            </View>
          </View>

          <View className="flex-row items-center gap-2">
            <View className="bg-gray-300 px-3 py-1.5 rounded-md">
              <Text className="text-sm font-semibold">Bloqueado</Text>
            </View>
            <TouchableOpacity className="bg-gray-200 p-2 rounded-md">
              <Ionicons name="chevron-down" size={18} />
            </TouchableOpacity>
          </View>
        </View>

        {/* CONTACTO 2 */}
        <View className="flex-row items-center justify-between mb-4">
          <View className="flex-row items-center gap-3">
            <Image
              source={{ uri: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRweFF2mawqxoOSEeywL3afy_J1o50wZWH90Q&s" }}
              className="w-12 h-12 rounded-full"
            />
            <View>
              <Text className="font-bold text-base">Ayanokouji</Text>
              <Text className="text-gray-500 text-sm">
                Bloqueado 15 Mar 2025
              </Text>
            </View>
          </View>

          <View className="flex-row items-center gap-2">
            <View className="bg-gray-300 px-3 py-1.5 rounded-md">
              <Text className="text-sm font-semibold">Bloqueado</Text>
            </View>
            <TouchableOpacity className="bg-gray-200 p-2 rounded-md">
              <Ionicons name="chevron-down" size={18} />
            </TouchableOpacity>
          </View>
        </View>

      </ScrollView>

      {/* FOOTER */}
      <View className="items-center mb-10">
        <Text className="font-bold text-base mb-1">
          Tienes 2 contacto bloqueado
        </Text>
        <Text className="text-gray-500 text-sm text-center px-8">
          Estos contactos no podrán enviarte mensajes
        </Text>

        <TouchableOpacity className="mt-6 bg-green-600 py-3 rounded-full w-full">
          <Text className="text-white text-center font-bold text-base">
            Gestionar bloqueados
          </Text>
        </TouchableOpacity>
      </View>

    </View>
  );
}