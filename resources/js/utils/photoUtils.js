export function getKidPhotoUrl(kidId, photoFilename) {
  if (photoFilename && kidId) {
    return `/kids/${kidId}/photo/${photoFilename}`
  }

  return `/images/kids/default.png`
}
