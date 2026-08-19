import { Hero } from "@/components/Hero";
import { Ticker } from "@/components/Ticker";
import { Venue } from "@/components/Venue";
import { Tech } from "@/components/Tech";
import { Audience } from "@/components/Audience";
import { Roadmap } from "@/components/Roadmap";
import { Interlude } from "@/components/Interlude";
import { Book } from "@/components/Book";

export default function Page() {
  return (
    <>
      <Hero />
      <Ticker />
      <Venue />
      <Tech />
      <Audience />
      <Roadmap />
      <Interlude />
      <Book />
    </>
  );
}
