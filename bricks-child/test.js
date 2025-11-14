function applyDiscount(price, discountPercent) {
    // ❌ Logic wrong: dividing discount percentage directly
    const discountAmount = price / discountPercent; 
    return price - discountAmount;
}

console.log(applyDiscount(100, 10));  
// Expected: 90  
// Actual: 90? No → returns wrong result