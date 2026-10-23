FROM maven:3.9-eclipse-temurin-21 AS build
WORKDIR /build
COPY pom.xml .
RUN --mount=type=cache,target=/root/.m2 mvn -q -B dependency:go-offline
COPY src ./src
RUN --mount=type=cache,target=/root/.m2 mvn -q -B -DskipTests package

FROM eclipse-temurin:21-jre
WORKDIR /app
COPY --from=build /build/target/shop.jar app.jar
EXPOSE 8080
ENTRYPOINT ["java", "-jar", "app.jar"]
