import React, { useEffect, useMemo, useRef, useState } from "react";
import {
  FlatList,
  Image,
  ImageSourcePropType,
  NativeScrollEvent,
  NativeSyntheticEvent,
  StyleSheet,
  Text,
  View,
  useWindowDimensions,
} from "react-native";

export type CarouselSlide = {
  id: string;
  image: ImageSourcePropType;
  title: string;
  description?: string;
};

type Props = {
  slides: CarouselSlide[];
  height?: number;
  autoplayMs?: number;
};

export default function WelcomeCarousel({
  slides,
  height = 220,
  autoplayMs = 3000,
}: Props) {
  const { width } = useWindowDimensions(); // ✅ ancho real dinámico
  const listRef = useRef<FlatList<CarouselSlide>>(null);

  // ✅ ancho REAL del carrusel (no el de la pantalla)
  const [pageW, setPageW] = useState<number>(0);

  const [activeIndex, setActiveIndex] = useState(0);
  const currentIndexRef = useRef(0);

  const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

  const stopAutoplay = () => {
    if (intervalRef.current) {
      clearInterval(intervalRef.current);
      intervalRef.current = null;
    }
  };

  const startAutoplay = () => {
    stopAutoplay();
    if (!slides || slides.length <= 1 || pageW <= 0) return;

    intervalRef.current = setInterval(() => {
      const next = (currentIndexRef.current + 1) % slides.length;

      listRef.current?.scrollToIndex({ index: next, animated: true });

      currentIndexRef.current = next;
      setActiveIndex(next);
    }, autoplayMs);
  };

  useEffect(() => {
    currentIndexRef.current = activeIndex;
  }, [activeIndex]);

  useEffect(() => {
    startAutoplay();
    return () => stopAutoplay();
  }, [slides?.length, autoplayMs]);

  // ✅ si cambia el ancho (rotación), reubica el índice actual
  useEffect(() => {
    requestAnimationFrame(() => {
      listRef.current?.scrollToIndex({
        index: currentIndexRef.current,
        animated: false,
      });
    });
  }, [width]);

  const onMomentumEnd = (e: NativeSyntheticEvent<NativeScrollEvent>) => {
    if (pageW <= 0) return;
    const x = e.nativeEvent.contentOffset.x;
    const index = Math.round(x / pageW);
    currentIndexRef.current = index;
    setActiveIndex(index);
  };

  const dots = useMemo(() => {
    return slides.map((s, i) => (
      <View
        key={s.id}
        style={[
          styles.dot,
          i === activeIndex ? styles.dotActive : styles.dotInactive,
        ]}
      />
    ));
  }, [slides, activeIndex]);

  // ✅ ancho de la tarjeta responsivo (con límite para tablets)
  const cardWidth = Math.min(width * 0.95, 520);
  const imageHeight = height * 0.62;

  return (
    <View
      style={[styles.wrapper, { height }]}
      onLayout={(e) => {
        const w = e.nativeEvent.layout.width;
        if (w && w !== pageW) setPageW(w);
      }}
    >
      <FlatList
        ref={listRef}
        data={slides}
        keyExtractor={(item) => item.id}
        horizontal
        showsHorizontalScrollIndicator={false}
        pagingEnabled

        // ✅ mejora el “snap” y evita medias cartas
        decelerationRate="fast"
        snapToInterval={pageW || 1}
        snapToAlignment="start"

        onMomentumScrollEnd={onMomentumEnd}
        onScrollBeginDrag={stopAutoplay}
        onScrollEndDrag={() => setTimeout(startAutoplay, 350)}
        getItemLayout={(_, index) => ({
          length: pageW || 0,
          offset: (pageW || 0) * index,
          index,
        })}
        onScrollToIndexFailed={(info) => {
          setTimeout(() => {
            listRef.current?.scrollToOffset({
              offset: info.index * (pageW || 0),
              animated: true,
            });
          }, 200);
        }}
        renderItem={({ item }) => (
          <View style={[styles.slide, { width, height }]}>
            <View style={[styles.card, { width: cardWidth }]}>
              <Image
                source={item.image}
                style={[styles.image, { height: imageHeight }]}
                resizeMode="cover"
              />
              <View style={styles.textBox}>
                <Text style={styles.title} numberOfLines={1}>
                  {item.title}
                </Text>
                {!!item.description && (
                  <Text style={styles.desc} numberOfLines={2}>
                    {item.description}
                  </Text>
                )}
              </View>
            </View>
          </View>
        )}
      />

      <View style={styles.dotsRow}>{dots}</View>
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: { width: "100%", justifyContent: "center" },
  slide: { justifyContent: "center", alignItems: "center" },
  card: {
    height: "100%",
    borderRadius: 18,
    backgroundColor: "#ffffff",
    overflow: "hidden",
    shadowColor: "#000",
    shadowOpacity: 0.12,
    shadowRadius: 10,
    shadowOffset: { width: 0, height: 6 },
    elevation: 6,
  },
  image: { width: "100%" },
  textBox: { paddingHorizontal: 14, paddingVertical: 12, gap: 6 },
  title: { fontWeight: "700", color: "#1f2937", fontSize: 18 },
  desc: { color: "#4b5563", fontSize: 14 },
  dotsRow: { marginTop: 10, flexDirection: "row", justifyContent: "center", gap: 8 },
  dot: { width: 8, height: 8, borderRadius: 999 },
  dotActive: { backgroundColor: "#32CD32", width: 18 },
  dotInactive: { backgroundColor: "#cbd5e1" },
});
