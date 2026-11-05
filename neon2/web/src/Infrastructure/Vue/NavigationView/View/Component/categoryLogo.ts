import bazydanych from "../../../../../../../../public/img/category/bazydanych.svg";
import cc from "../../../../../../../../public/img/category/cc.svg";
import cinet from "../../../../../../../../public/img/category/cinet.svg";
import delphiipascal from "../../../../../../../../public/img/category/delphiipascal.svg";
import gamedev from "../../../../../../../../public/img/category/gamedev.svg";
import go from "../../../../../../../../public/img/category/go.svg";
import java from "../../../../../../../../public/img/category/java.svg";
import javascript from "../../../../../../../../public/img/category/javascript.svg";
import mobilne from "../../../../../../../../public/img/category/mobilne.svg";
import php from "../../../../../../../../public/img/category/php.svg";
import python from "../../../../../../../../public/img/category/python.svg";
import rust from "../../../../../../../../public/img/category/rust.svg";
import webmastering from "../../../../../../../../public/img/category/webmastering.svg";

const logos: Record<string, string> = {
  bazydanych, cc, cinet, delphiipascal, gamedev, go, java,
  javascript, mobilne, php, python, rust, webmastering,
};

export function categoryLogo(categoryTitle: string): string|null {
  return logos[categoryTitle.toLowerCase().replace(/[^a-z]/g, '')] ?? null;
}
